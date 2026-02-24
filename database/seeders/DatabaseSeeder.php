<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SubDivision;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SubDivisionSeeder::class,
            UserSeeder::class,
            AssignmentSeeder::class,
            AttendedSeeder::class,
            AssignmentAttendedSeeder::class,
            AssignmentUserSeeder::class,
        ]);
    }
}
