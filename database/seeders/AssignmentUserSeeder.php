<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssignmentUser;
use App\Models\Assignment;
use App\Models\User;

class AssignmentUserSeeder extends Seeder
{
    public function run(): void
    {
        $assignments = Assignment::all();
        $users = User::all();
        foreach ($assignments as $assignment) {
            $userIds = $users->random(rand(1, 5))->pluck('id')->toArray();
            foreach ($userIds as $userId) {
                AssignmentUser::create([
                    'user_id' => $userId,
                    'assignment_id' => $assignment->id,
                    'departure_location' => 'Lokasi Berangkat ' . $assignment->id,
                    'destination_location' => 'Lokasi Tujuan ' . $assignment->id,
                    'is_verified' => rand(0, 1),
                ]);
            }
        }
    }
}
