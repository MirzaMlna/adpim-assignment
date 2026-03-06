<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attended;

class AttendedSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name' => 'H. Muhidin',
                'rank' => 'Gubernur',
                'rank_abbreviation' => 'GUB'
            ],
            [
                'name' => 'H. Hasnuryadi Sulaiman',
                'rank' => 'Wakil Gubernur',
                'rank_abbreviation' => 'WAGUB'
            ],
            [
                'name' => 'M. Syarifuddin, M.Pd',
                'rank' => 'Sekretaris Daerah',
                'rank_abbreviation' => 'SEKDA'
            ],
        ];

        foreach ($data as $item) {
            Attended::create($item);
        }
    }
}
