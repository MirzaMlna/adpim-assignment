<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assignment;
use App\Models\Attended;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AssignmentAttendedSeeder extends Seeder
{
    public function run(): void
    {
        $assignments = Assignment::all();
        $attendeds = Attended::all();
        foreach ($assignments as $assignment) {
            $ids = $attendeds->random(rand(1, 3))->pluck('id')->toArray();
            foreach ($ids as $attendedId) {
                DB::table('assignment_attended')->insert([
                    'assignment_id' => $assignment->id,
                    'attended_id' => $attendedId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
