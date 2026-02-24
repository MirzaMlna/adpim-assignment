<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubDivision;
use App\Models\User;
use App\Models\Assignment;
use App\Models\Attended;
use App\Models\AssignmentUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SubDivisionSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Sub Bagian Umum',
            'Sub Bagian Protokol',
            'Sub Bagian Dokumentasi',
            'Sub Bagian Humas',
            'Sub Bagian Rumah Tangga',
        ];
        foreach ($names as $name) {
            SubDivision::firstOrCreate(['name' => $name]);
        }
    }
}
