<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class AssignmentUserController extends Controller
{
    public function index()
    {
        $assignments = Assignment::with(['assignmentUsers.user', 'attendeds'])
            ->latest()
            ->paginate(10);

        return view('assignment-users.index', compact('assignments'));
    }

    public function create(Request $request)
    {
        $users = User::orderBy('name')->get();
        $lockedAssignment = null;

        if ($request->filled('assignment_id')) {
            $lockedAssignment = Assignment::with('assignmentUsers')
                ->find($request->integer('assignment_id'));

            if (! $lockedAssignment) {
                return redirect()->route('assignments.index')
                    ->with('error', 'Giat tidak ditemukan.');
            }

            if ($lockedAssignment->assignmentUsers->isNotEmpty()) {
                return redirect()
                    ->route('assignment-users.edit', $lockedAssignment->assignmentUsers->first()->id)
                    ->with('info', 'Giat ini sudah ditugaskan. Silakan ubah petugas di halaman edit.');
            }
        }

        $assignments = $lockedAssignment
            ? Assignment::whereKey($lockedAssignment->id)->get()
            : Assignment::whereDoesntHave('assignmentUsers')
                ->latest('date')
                ->get();

        if ($users->isEmpty()) {
            session()->now('warning', 'Data petugas belum tersedia. Tambahkan data petugas terlebih dahulu.');
        } elseif ($assignments->isEmpty()) {
            session()->now('warning', 'Semua assignment sudah ditugaskan. Silakan ubah data melalui menu edit.');
        }

        return view('assignment-users.create', compact('users', 'assignments', 'lockedAssignment'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'user_id' => 'required|array|min:1|max:5',
                'user_id.*' => 'required|integer|distinct|exists:users,id',
                'assignment_id' => 'required|integer|exists:assignments,id',
            ],
            $this->messagesForStore()
        );

        $userIds = collect($validated['user_id'])
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return back()->withInput()->with('warning', 'Pilih minimal satu petugas.');
        }

        if (AssignmentUser::where('assignment_id', $validated['assignment_id'])->exists()) {
            return back()
                ->withInput()
                ->with('warning', 'Assignment tersebut sudah ditugaskan. Gunakan menu edit untuk memperbarui.');
        }

        try {
            DB::transaction(function () use ($validated, $userIds) {
                $now = now();
                $rows = $userIds->map(fn($userId) => [
                    'user_id' => $userId,
                    'assignment_id' => $validated['assignment_id'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                AssignmentUser::insert($rows);
            });
        } catch (Throwable $e) {
            report($e);
            return back()
                ->withInput()
                ->with('error', 'Data penugasan gagal disimpan. Silakan coba lagi.');
        }

        return redirect()->route('assignments.index')
            ->with('success', 'Petugas untuk giat berhasil disimpan.');
    }

    public function show(AssignmentUser $assignment_user)
    {
        return redirect()
            ->route('assignment-users.edit', $assignment_user->id)
            ->with('info', 'Detail penugasan ditampilkan melalui halaman edit.');
    }

    public function edit(AssignmentUser $assignment_user)
    {
        $users = User::orderBy('name')->get();
        $selectedAssignmentId = (int) $assignment_user->assignment_id;
        $lockedAssignment = Assignment::find($selectedAssignmentId);
        $assignedUserIds = AssignmentUser::where('assignment_id', $selectedAssignmentId)
            ->pluck('user_id')
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();

        if ($users->isEmpty()) {
            session()->now('warning', 'Data petugas tidak ditemukan.');
        }

        return view('assignment-users.edit', compact('assignment_user', 'users', 'assignedUserIds', 'lockedAssignment'));
    }

    public function update(Request $request, AssignmentUser $assignment_user)
    {
        $originalAssignmentId = (int) $assignment_user->assignment_id;
        $validated = $request->validate(
            [
                'user_id' => 'required|array|min:1|max:5',
                'user_id.*' => 'required|integer|distinct|exists:users,id',
            ],
            $this->messagesForUpdate()
        );

        $newUserIds = collect($validated['user_id'])
            ->map(fn($id) => (int) $id)
            ->unique()
            ->sort()
            ->values();
        $currentUserIds = AssignmentUser::where('assignment_id', $originalAssignmentId)
            ->pluck('user_id')
            ->map(fn($id) => (int) $id)
            ->sort()
            ->values();

        if ($newUserIds->all() === $currentUserIds->all()) {
            return back()->with('warning', 'Tidak ada perubahan data untuk disimpan.');
        }

        try {
            DB::transaction(function () use ($originalAssignmentId, $newUserIds) {
                AssignmentUser::where('assignment_id', $originalAssignmentId)->delete();

                $now = now();
                $rows = $newUserIds->map(fn($userId) => [
                    'user_id' => $userId,
                    'assignment_id' => $originalAssignmentId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                AssignmentUser::insert($rows);
            });
        } catch (Throwable $e) {
            report($e);
            return back()
                ->withInput()
                ->with('error', 'Data penugasan gagal diperbarui. Silakan coba lagi.');
        }

        return redirect()->route('assignments.index')
            ->with('success', 'Petugas untuk giat berhasil diperbarui.');
    }

    public function destroy(AssignmentUser $assignment_user)
    {
        try {
            $deletedRows = AssignmentUser::where('assignment_id', $assignment_user->assignment_id)->delete();
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'Data penugasan gagal dihapus. Silakan coba lagi.');
        }

        if ($deletedRows === 0) {
            return back()->with('warning', 'Tidak ada data penugasan yang dihapus.');
        }

        return redirect()->route('assignments.index')
            ->with('success', "Data penugasan berhasil dihapus ({$deletedRows} petugas).");
    }

    private function messagesForStore(): array
    {
        return [
            'user_id.required' => 'Petugas wajib dipilih minimal 1.',
            'user_id.array' => 'Format petugas tidak valid.',
            'user_id.min' => 'Petugas wajib dipilih minimal 1.',
            'user_id.max' => 'Maksimal 5 petugas.',
            'user_id.*.distinct' => 'Petugas tidak boleh duplikat.',
            'assignment_id.required' => 'Assignment wajib dipilih.',
        ];
    }

    private function messagesForUpdate(): array
    {
        return [
            'user_id.required' => 'Petugas wajib dipilih minimal 1.',
            'user_id.array' => 'Format petugas tidak valid.',
            'user_id.min' => 'Petugas wajib dipilih minimal 1.',
            'user_id.max' => 'Maksimal 5 petugas.',
            'user_id.*.distinct' => 'Petugas tidak boleh duplikat.',
        ];
    }
}
