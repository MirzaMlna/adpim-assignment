<?php

namespace Database\Seeders;

use App\Models\SubDivision;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $subDivision = SubDivision::firstOrCreate([
            'name' => 'Dokumentasi Pimpinan',
        ]);

        User::updateOrCreate(
            ['email' => 'test@adpim.com'],
            [
                'sub_division_id' => $subDivision->id,
                'password' => 'password',
                'nip' => '-',
                'name' => 'Super Admin',
                'rank' => 'Administrator',
                'job_title' => 'Super Admin',
                'assignment_regulation_level' => '-',
                'role' => 'admin',
                'is_active' => true,
                'note' => 'Akun super admin (seeder)',
            ]
        );
    }
}
