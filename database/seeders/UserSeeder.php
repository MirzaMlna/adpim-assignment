<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SubDivision;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $subs = SubDivision::all();
        if ($subs->count() == 0) {
            $subs = collect([
                SubDivision::create(['name' => 'Sub Bidang Dokumentasi Pimpinan']),
            ]);
        }
        $roles = ['ADMIN', 'STAFF', 'PIMPINAN ADPIM'];
        foreach (range(1, 10) as $i) {
            User::create([
                'sub_division_id' => $subs->random()->id,
                'email' => 'user' . $i . '@adpim.com',
                'password' => 'password',
                'nip' => '12345678' . $i,
                'name' => fake()->name(),
                'rank' => 'Rank ' . $i,
                'job_title' => 'Job Title ' . $i,
                'role' => $roles[array_rand($roles)],
                'is_active' => true,
                'note' => 'Seeder user',
            ]);
        }
    }
}
