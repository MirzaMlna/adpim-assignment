<?php

namespace App\Http\Controllers;

use App\Models\Attended;
use Illuminate\Http\Request;

class AttendedController extends Controller
{
    public function index()
    {
        $attendeds = Attended::latest()->paginate(10);
        return view('attendeds.index', compact('attendeds'));
    }

    public function create()
    {
        return view('attendeds.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rank' => 'required|string|max:255',
            'rank_abbreviation' => 'required|string|max:255',
        ]);

        Attended::create($request->all());

        return redirect()->route('attendeds.index')
            ->with('success', 'Data pimpinan berhasil ditambahkan.');
    }

    public function edit(Attended $attended)
    {
        return view('attendeds.edit', compact('attended'));
    }

    public function update(Request $request, Attended $attended)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rank' => 'required|string|max:255',
            'rank_abbreviation' => 'required|string|max:255',
        ]);

        $attended->update($request->all());

        return redirect()->route('attendeds.index')
            ->with('success', 'Data pimpinan berhasil diperbarui.');
    }

    public function destroy(Attended $attended)
    {
        $attended->delete();

        return redirect()->route('attendeds.index')
            ->with('success', 'Data pimpinan berhasil dihapus.');
    }
}
