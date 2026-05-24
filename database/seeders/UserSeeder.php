<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Utama',
            'email' => 'admin@smkglobin.sch.id',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $staff = User::factory()->create([
            'name' => 'Staff TU',
            'email' => 'staff@smkglobin.sch.id',
            'password' => bcrypt('password'),
        ]);
        $staff->assignRole('staff');

        $walasX = User::factory()->create([
            'name' => 'Budi Santoso, S.Pd.',
            'email' => 'walas@smkglobin.sch.id',
            'password' => bcrypt('password'),
        ]);
        $walasX->assignRole('wali_kelas');

        $walasXI = User::factory()->create([
            'name' => 'Dewi Lestari, S.Pd.',
            'email' => 'walas.xi@smkglobin.sch.id',
            'password' => bcrypt('password'),
        ]);
        $walasXI->assignRole('wali_kelas');
    }
}
