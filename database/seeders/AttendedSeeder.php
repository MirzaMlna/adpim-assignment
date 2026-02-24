<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attended;

class AttendedSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 10) as $i) {
            Attended::create([
                'name' => 'Attended ' . $i,
                'rank' => 'Rank ' . $i,
                'rank_abbreviation' => 'R' . $i,
            ]);
        }
    }
}
