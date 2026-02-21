<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Attended;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index()
    {
        $assignments = Assignment::with('attendeds')
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
            'attended_ids' => 'required|array',
            'attended_ids.*' => 'exists:attendeds,id',
            'title' => 'required',
            'agency' => 'required',
            'date' => 'required|date',
            'time' => 'required',
            'fee_per_day' => 'required|numeric'
        ]);

        $datePart = date('dmY', strtotime($request->date));
        $last = Assignment::whereDate('date', $request->date)->count() + 1;
        $increment = str_pad($last, 3, '0', STR_PAD_LEFT);

        $code = "GIAT-{$datePart}-{$increment}";

        $assignment = Assignment::create([
            'code' => $code,
            'title' => $request->title,
            'agency' => $request->agency,
            'date' => $request->date,
            'time' => $request->time,
            'day_count' => $request->day_count ?? 1,
            'location' => $request->location,
            'location_detail' => $request->location_detail,
            'fee_per_day' => $request->fee_per_day,
            'description' => $request->description,
        ]);

        $assignment->attendeds()->attach($request->attended_ids);

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
            'attended_ids' => 'required|array',
            'attended_ids.*' => 'exists:attendeds,id',
            'title' => 'required',
            'agency' => 'required',
            'date' => 'required|date',
            'time' => 'required',
            'fee_per_day' => 'required|numeric'
        ]);

        $assignment->update($request->except('attended_ids'));

        $assignment->attendeds()->sync($request->attended_ids);

        return redirect()->route('assignments.index')
            ->with('success', 'Data tugas berhasil diperbarui');
    }

    public function show(Assignment $assignment)
    {
        $assignment->load('attendeds');

        return view('assignments.show', compact('assignment'));
    }

    public function destroy(Assignment $assignment)
    {
        $assignment->delete();

        return redirect()->route('assignments.index')
            ->with('success', 'Data tugas berhasil dihapus');
    }
}
