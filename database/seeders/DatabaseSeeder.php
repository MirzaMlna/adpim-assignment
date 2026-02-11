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
        // Pastikan ada minimal 1 Sub Division
        $subDivision = SubDivision::first();

        if (!$subDivision) {
            $subDivision = SubDivision::create([
                'name' => 'Sub Bagian Umum',
            ]);
        }

        User::create([
            'sub_division_id' => $subDivision->id,
            'email' => 'test@adpim.com',
            'password' => 'password',
            'nip' => '1234567890',
            'name' => 'TEST USER',
            'rank' => 'Pembina',
            'job_title' => 'Administrator',
            'role' => 'ADMIN',
            'is_active' => true,
            'note' => 'Akun default sistem',
        ]);
    }
}
