<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentUser;
use App\Models\User;
use Illuminate\Http\Request;

class AssignmentUserController extends Controller
{
    public function index()
    {
        $assignments = Assignment::with(['assignmentUsers.user'])
            ->latest()
            ->paginate(10);

        return view('assignment-users.index', compact('assignments'));
    }

    public function create()
    {
        $users = User::all();
        $assignments = Assignment::whereDoesntHave('assignmentUsers')->get();

        return view('assignment-users.create', compact('users', 'assignments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|array|min:1|max:5',
            'user_id.*' => 'exists:users,id',
            'assignment_id' => 'required|exists:assignments,id',
        ]);

        foreach ($request->user_id as $userId) {
            AssignmentUser::create([
                'user_id' => $userId,
                'assignment_id' => $request->assignment_id,
            ]);
        }

        return redirect()->route('assignment-users.index')
            ->with('success', 'Data berhasil ditambahkan');
    }

    public function edit(AssignmentUser $assignment_user)
    {
        $users = User::all();
        $assignments = Assignment::all();

        return view('assignment-users.edit', compact('assignment_user', 'users', 'assignments'));
    }

    public function update(Request $request, AssignmentUser $assignment_user)
    {
        $request->validate([
            'user_id' => 'required|array|min:1|max:5',
            'user_id.*' => 'exists:users,id',
            'assignment_id' => 'required|exists:assignments,id',
        ]);

        AssignmentUser::where('assignment_id', $assignment_user->assignment_id)->delete();

        foreach ($request->user_id as $userId) {
            AssignmentUser::create([
                'user_id' => $userId,
                'assignment_id' => $request->assignment_id,
            ]);
        }

        return redirect()->route('assignment-users.index')
            ->with('success', 'Data berhasil diperbarui');
    }
    public function destroy(AssignmentUser $assignment_user)
    {
        $assignment_user->delete();

        return back()->with('success', 'Data berhasil dihapus');
    }
}
