<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assignment;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 10) as $i) {
            Assignment::create([
                'code' => 'ASSIGN-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'title' => 'Penugasan ' . $i,
                'agency' => 'Instansi ' . $i,
                'date' => now()->addDays($i),
                'time' => now()->setTime(rand(7, 10), 0),
                'day_count' => rand(1, 5),
                'location' => 'Lokasi ' . $i,
                'location_detail' => 'Detail Lokasi ' . $i,
                'fee_per_day' => rand(100000, 500000),
                'region_classification' => 'dalam_daerah',
                'description' => 'Deskripsi penugasan ' . $i,
            ]);
        }
    }
}
