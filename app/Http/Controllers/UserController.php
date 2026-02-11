<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SubDivision;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('subDivision')->latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $subDivisions = SubDivision::all();
        return view('users.create', compact('subDivisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sub_division_id' => 'required|exists:sub_divisions,id',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'nip' => 'required|numeric',
            'name' => 'required',
            'rank' => 'required',
            'job_title' => 'required',
            'role' => 'required',
        ]);

        User::create([
            'sub_division_id' => $request->sub_division_id,
            'email' => $request->email,
            'password' => $request->password, // otomatis hash
            'nip' => $request->nip,
            'name' => $request->name,
            'rank' => $request->rank,
            'job_title' => $request->job_title,
            'role' => $request->role,
            'is_active' => $request->has('is_active'),
            'note' => $request->note,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }


    public function edit(User $user)
    {
        $subDivisions = SubDivision::all();
        return view('users.edit', compact('user', 'subDivisions'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'sub_division_id' => 'required|exists:sub_divisions,id',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'nip' => 'required|numeric',
            'name' => 'required',
            'rank' => 'required',
            'job_title' => 'required',
            'role' => 'required',
        ]);

        $data = $request->except('password');

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $data['is_active'] = $request->has('is_active');

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }


    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
