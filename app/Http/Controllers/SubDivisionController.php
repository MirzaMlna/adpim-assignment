<?php

namespace App\Http\Controllers;

use App\Models\SubDivision;
use Illuminate\Http\Request;

class SubDivisionController extends Controller
{
    public function index()
    {
        $subDivisions = SubDivision::latest()->paginate(10);
        return view('sub_divisions.index', compact('subDivisions'));
    }

    public function create()
    {
        return view('sub_divisions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        SubDivision::create($request->all());

        return redirect()->route('sub-divisions.index')
            ->with('success', 'Sub Bidang berhasil ditambahkan.');
    }

    public function edit(SubDivision $subDivision)
    {
        return view('sub_divisions.edit', compact('subDivision'));
    }

    public function update(Request $request, SubDivision $subDivision)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $subDivision->update($request->all());

        return redirect()->route('sub-divisions.index')
            ->with('success', 'Sub Bidang berhasil diperbarui.');
    }

    public function destroy(SubDivision $subDivision)
    {
        $subDivision->delete();

        return redirect()->route('sub-divisions.index')
            ->with('success', 'Sub Bidang berhasil dihapus.');
    }
}
