<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Attended;
use App\Services\SppdDocxExporter;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class AssignmentController extends Controller
{
    public function index()
    {
        $assignments = Assignment::query()
            ->with([
                'attendeds:id,rank_abbreviation',
                'assignmentUsers:id,assignment_id',
            ])
            ->latest('date')
            ->paginate(10);

        return view('assignments.index', compact('assignments'));
    }

    public function create()
    {
        $attendeds = Attended::query()->orderBy('name')->get(['id', 'name']);

        return view('assignments.create', compact('attendeds'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());
        $resolvedReturnDate = $this->resolveReturnDate($validated['boarding_date'], (int) $validated['day_count']);

        try {
            $assignment = null;
            $attendedIds = collect($validated['attended_ids'])->unique()->values()->all();

            $payload = $this->buildAssignmentPayload($validated, $resolvedReturnDate);
            $attempt = 0;
            $maxAttempt = 3;

            do {
                try {
                    DB::transaction(function () use (&$assignment, $payload, $attendedIds) {
                        $assignment = Assignment::create([
                            ...$payload,
                            'code' => $this->generateAssignmentCode($payload['date']),
                        ]);

                        $assignment->attendeds()->attach($attendedIds);
                    });

                    break;
                } catch (QueryException $queryException) {
                    $attempt++;
                    if (! $this->isAssignmentCodeCollision($queryException) || $attempt >= $maxAttempt) {
                        throw $queryException;
                    }
                }
            } while ($attempt < $maxAttempt);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Data tugas gagal disimpan. Silakan coba lagi.');
        }

        $this->forgetDashboardCacheForMonth($validated['date']);

        return redirect()->route('assignments.index')
            ->with('success', 'Data tugas berhasil ditambahkan');
    }

    public function edit(Assignment $assignment)
    {
        $assignment->loadMissing('attendeds:id');
        $attendeds = Attended::query()->orderBy('name')->get(['id', 'name']);

        return view('assignments.edit', compact('assignment', 'attendeds'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $originalDate = optional($assignment->date)->format('Y-m-d');
        $validated = $request->validate($this->rules(), $this->messages());
        $resolvedReturnDate = $this->resolveReturnDate($validated['boarding_date'], (int) $validated['day_count']);
        $payload = $this->buildAssignmentPayload($validated, $resolvedReturnDate);

        $attendedIds = collect($validated['attended_ids'])->unique()->values()->all();
        $currentAttendedIds = $assignment->attendeds()
            ->pluck('attendeds.id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();
        $incomingAttendedIds = collect($attendedIds)
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $isUnchanged = $assignment->title === $validated['title']
            && $assignment->agency === $validated['agency']
            && (string) optional($assignment->date)->format('Y-m-d') === (string) $validated['date']
            && (string) optional($assignment->boarding_date)->format('Y-m-d') === (string) $validated['boarding_date']
            && (string) optional($assignment->return_date)->format('Y-m-d') === (string) $resolvedReturnDate
            && $assignment->transportation === $validated['transportation']
            && $assignment->time === $validated['time']
            && (int) $assignment->day_count === (int) $validated['day_count']
            && $assignment->location === $validated['location']
            && (string) $assignment->location_detail === (string) ($validated['location_detail'] ?? null)
            && (float) $assignment->fee_per_day === (float) $validated['fee_per_day']
            && $assignment->region_classification === $validated['region_classification']
            && (string) $assignment->description === (string) ($validated['description'] ?? null)
            && $currentAttendedIds === $incomingAttendedIds;

        if ($isUnchanged) {
            return back()->with('warning', 'Tidak ada perubahan data untuk disimpan.');
        }

        try {
            DB::transaction(function () use ($assignment, $payload, $attendedIds) {
                $assignment->update($payload);

                $assignment->attendeds()->sync($attendedIds);
            });
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Perubahan data tugas gagal disimpan. Silakan coba lagi.');
        }

        $this->forgetDashboardCacheForMonth($originalDate ?: $validated['date']);
        $this->forgetDashboardCacheForMonth($validated['date']);

        return redirect()->route('assignments.index')
            ->with('success', 'Data tugas berhasil diperbarui');
    }

    public function show(Assignment $assignment)
    {
        $assignment->load([
            'attendeds:id,name,rank_abbreviation',
            'assignmentUsers.user:id,name',
        ]);

        return view('assignments.show', compact('assignment'));
    }

    public function printSppd(Assignment $assignment, SppdDocxExporter $exporter)
    {
        $assignment->load([
            'assignmentUsers.user:id,name,nip,rank,job_title,assignment_regulation_level',
            'attendeds:id,name,rank,rank_abbreviation',
        ]);

        if ($assignment->assignmentUsers->isEmpty()) {
            return back()->with('warning', 'Petugas belum ditugaskan. Tugaskan petugas terlebih dahulu sebelum cetak SPT/SPPD.');
        }

        try {
            $outputPath = $exporter->export($assignment);
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal membuat file SPT/SPPD. Periksa template LEMBAR_SPT.docx atau LEMBAR_SPPD.docx lalu coba lagi.');
        }

        $downloadPrefix = $assignment->region_classification === 'dalam_daerah' ? 'SPT' : 'SPPD';
        $downloadName = $downloadPrefix.'-'.$assignment->code.'.docx';

        return response()->download($outputPath, $downloadName)->deleteFileAfterSend(true);
    }

    public function destroy(Assignment $assignment)
    {
        try {
            $assignedUserCount = $assignment->assignmentUsers()->count();
            $assignment->delete();
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('assignments.index')
                ->with('error', 'Data tugas gagal dihapus. Silakan coba lagi.');
        }

        $this->forgetDashboardCacheForMonth($assignment->date?->format('Y-m-d'));

        return redirect()->route('assignments.index')
            ->with(
                'success',
                $assignedUserCount > 0
                    ? "Data tugas berhasil dihapus beserta {$assignedUserCount} data penugasan."
                    : 'Data tugas berhasil dihapus.'
            );
    }

    private function generateAssignmentCode(string $date): string
    {
        $datePart = date('dmY', strtotime($date));
        $prefix = "GIAT-{$datePart}-";

        $lastCode = Assignment::query()
            ->where('code', 'like', $prefix.'%')
            ->orderByDesc('code')
            ->value('code');

        $nextSequence = 1;
        if (is_string($lastCode) && preg_match('/(\d{3})$/', $lastCode, $matches) === 1) {
            $nextSequence = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
    }

    private function buildAssignmentPayload(array $validated, string $resolvedReturnDate): array
    {
        return [
            'title' => $validated['title'],
            'agency' => $validated['agency'],
            'date' => $validated['date'],
            'boarding_date' => $validated['boarding_date'],
            'return_date' => $resolvedReturnDate,
            'transportation' => $validated['transportation'],
            'time' => $validated['time'],
            'day_count' => $validated['day_count'],
            'location' => $validated['location'],
            'location_detail' => $validated['location_detail'] ?? null,
            'fee_per_day' => $validated['fee_per_day'],
            'description' => $validated['description'] ?? null,
            'region_classification' => $validated['region_classification'],
        ];
    }

    private function isAssignmentCodeCollision(QueryException $queryException): bool
    {
        $sqlState = $queryException->errorInfo[0] ?? null;
        $message = $queryException->getMessage();

        return $sqlState === '23000'
            && str_contains(strtolower((string) $message), 'assignments_code_unique');
    }

    private function forgetDashboardCacheForMonth(?string $date): void
    {
        if (! $date) {
            return;
        }

        $monthKey = Carbon::parse($date)->format('Y-m');
        Cache::forget('dashboard:summary:'.$monthKey);
    }

    private function rules(): array
    {
        return [
            'attended_ids' => 'required|array|min:1',
            'attended_ids.*' => 'required|integer|distinct|exists:attendeds,id',
            'title' => 'required|string|max:255',
            'agency' => 'required|string|max:255',
            'date' => 'required|date',
            'boarding_date' => 'required|date',
            'transportation' => 'required|string|max:255',
            'time' => 'required|date_format:H:i',
            'day_count' => 'required|integer|min:1',
            'location' => 'required|string|max:255',
            'location_detail' => 'nullable|string|max:255',
            'fee_per_day' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'region_classification' => 'required|in:dalam_daerah,dalam_daerah_kabupaten,luar_daerah',
        ];
    }

    private function messages(): array
    {
        return [
            'attended_ids.required' => 'Pimpinan wajib dipilih minimal 1.',
            'attended_ids.min' => 'Pimpinan wajib dipilih minimal 1.',
            'attended_ids.*.distinct' => 'Pimpinan tidak boleh duplikat.',
            'title.required' => 'Judul kegiatan wajib diisi.',
            'agency.required' => 'Penyelenggara wajib diisi.',
            'date.required' => 'Tanggal wajib diisi.',
            'boarding_date.required' => 'Tanggal berangkat petugas wajib diisi.',
            'transportation.required' => 'Transportasi wajib diisi.',
            'time.required' => 'Jam wajib diisi.',
            'time.date_format' => 'Format jam tidak valid.',
            'day_count.required' => 'Lama hari wajib diisi.',
            'day_count.min' => 'Lama hari minimal 1.',
            'location.required' => 'Lokasi wajib diisi.',
            'fee_per_day.required' => 'Bayaran per hari wajib diisi.',
            'fee_per_day.min' => 'Bayaran per hari tidak boleh minus.',
            'region_classification.required' => 'Klasifikasi wilayah wajib dipilih.',
        ];
    }

    private function resolveReturnDate(string $boardingDate, int $dayCount): string
    {
        $safeDayCount = max(1, $dayCount);
        $returnDate = Carbon::parse($boardingDate)->addDays($safeDayCount - 1);

        return $returnDate->format('Y-m-d');
    }
}
