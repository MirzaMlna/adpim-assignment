<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Attended;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index()
    {
        $assignments = Assignment::with('attended')
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
        $request->validate([
            'attended_id' => 'required',
            'code' => 'required|unique:assignments',
            'title' => 'required',
            'agency' => 'required',
            'date' => 'required|date',
            'time' => 'required',
            'fee_per_day' => 'required|numeric'
        ]);

        Assignment::create($request->all());

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
        $request->validate([
            'attended_id' => 'required',
            'code' => 'required|unique:assignments,code,' . $assignment->id,
            'title' => 'required',
            'agency' => 'required',
            'date' => 'required|date',
            'time' => 'required',
            'fee_per_day' => 'required|numeric'
        ]);

        $assignment->update($request->all());

        return redirect()->route('assignments.index')
            ->with('success', 'Data tugas berhasil diperbarui');
    }

    public function destroy(Assignment $assignment)
    {
        $assignment->delete();

        return redirect()->route('assignments.index')
            ->with('success', 'Data tugas berhasil dihapus');
    }
}
