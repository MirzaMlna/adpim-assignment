<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Attended;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class AssignmentController extends Controller
{
    public function index()
    {
        $assignments = Assignment::with(['attendeds', 'assignmentUsers'])
            ->latest()
            ->paginate(10);

        return view('assignments.index', compact('assignments'));
    }

    public function create()
    {
        $attendeds = Attended::all();
        return view('assignments.create', compact('attendeds'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        try {
            $assignment = null;
            $attendedIds = collect($validated['attended_ids'])->unique()->values()->all();

            DB::transaction(function () use (&$assignment, $validated, $attendedIds) {
                $assignment = Assignment::create([
                    'code' => $this->generateAssignmentCode($validated['date']),
                    'title' => $validated['title'],
                    'agency' => $validated['agency'],
                    'date' => $validated['date'],
                    'time' => $validated['time'],
                    'day_count' => $validated['day_count'],
                    'location' => $validated['location'],
                    'location_detail' => $validated['location_detail'] ?? null,
                    'fee_per_day' => $validated['fee_per_day'],
                    'description' => $validated['description'] ?? null,
                    'region_classification' => $validated['region_classification'],
                ]);

                $assignment->attendeds()->attach($attendedIds);
            });
        } catch (Throwable $e) {
            report($e);
            return back()
                ->withInput()
                ->with('error', 'Data tugas gagal disimpan. Silakan coba lagi.');
        }

        return redirect()->route('assignments.index')
            ->with('success', 'Data tugas berhasil ditambahkan');
    }

    public function edit(Assignment $assignment)
    {
        $attendeds = Attended::all();
        return view('assignments.edit', compact('assignment', 'attendeds'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $attendedIds = collect($validated['attended_ids'])->unique()->values()->all();
        $currentAttendedIds = $assignment->attendeds()
            ->pluck('attendeds.id')
            ->map(fn($id) => (int) $id)
            ->sort()
            ->values()
            ->all();
        $incomingAttendedIds = collect($attendedIds)
            ->map(fn($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $isUnchanged = $assignment->title === $validated['title']
            && $assignment->agency === $validated['agency']
            && $assignment->date === $validated['date']
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
            DB::transaction(function () use ($assignment, $validated, $attendedIds) {
                $assignment->update([
                    'title' => $validated['title'],
                    'agency' => $validated['agency'],
                    'date' => $validated['date'],
                    'time' => $validated['time'],
                    'day_count' => $validated['day_count'],
                    'location' => $validated['location'],
                    'location_detail' => $validated['location_detail'] ?? null,
                    'fee_per_day' => $validated['fee_per_day'],
                    'description' => $validated['description'] ?? null,
                    'region_classification' => $validated['region_classification'],
                ]);

                $assignment->attendeds()->sync($attendedIds);
            });
        } catch (Throwable $e) {
            report($e);
            return back()
                ->withInput()
                ->with('error', 'Perubahan data tugas gagal disimpan. Silakan coba lagi.');
        }

        return redirect()->route('assignments.index')
            ->with('success', 'Data tugas berhasil diperbarui');
    }

    public function show(Assignment $assignment)
    {
        $assignment->load(['attendeds', 'assignmentUsers.user']);

        return view('assignments.show', compact('assignment'));
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
        $sequence = Assignment::whereDate('date', $date)->count() + 1;

        do {
            $increment = str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
            $code = "GIAT-{$datePart}-{$increment}";
            $isUsed = Assignment::where('code', $code)->exists();
            $sequence++;
        } while ($isUsed);

        return $code;
    }

    private function rules(): array
    {
        return [
            'attended_ids' => 'required|array|min:1',
            'attended_ids.*' => 'required|integer|distinct|exists:attendeds,id',
            'title' => 'required|string|max:255',
            'agency' => 'required|string|max:255',
            'date' => 'required|date',
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
}
