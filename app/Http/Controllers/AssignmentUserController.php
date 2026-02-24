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
        $data = AssignmentUser::with(['user', 'assignment'])->latest()->paginate(10);
        return view('assignment-users.index', compact('data'));
    }

    public function create()
    {
        $users = User::all();
        $assignments = Assignment::all();
        return view('assignment-users.create', compact('users', 'assignments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|array|min:1|max:5',
            'user_id.*' => 'exists:users,id',
            'assignment_id' => 'required',
            'departure_location' => 'required',
            'destination_location' => 'required',
        ]);

        foreach ($request->user_id as $userId) {
            AssignmentUser::create([
                'user_id' => $userId,
                'assignment_id' => $request->assignment_id,
                'departure_location' => $request->departure_location,
                'destination_location' => $request->destination_location,
                'is_verified' => $request->has('is_verified'),
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
            'departure_location' => 'required',
            'destination_location' => 'required',
        ]);

        $assignment_user->update($request->all());

        return redirect()->route('assignment-users.index')
            ->with('success', 'Data berhasil diperbarui');
    }

    public function destroy(AssignmentUser $assignment_user)
    {
        $assignment_user->delete();
        return back()->with('success', 'Data berhasil dihapus');
    }
}
