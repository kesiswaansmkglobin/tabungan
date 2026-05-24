<?php

namespace Database\Seeders;

use App\Models\SchoolData;
use Illuminate\Database\Seeder;

class SchoolDataSeeder extends Seeder
{
    public function run(): void
    {
        SchoolData::create([
            'name' => 'SMK Globin',
            'headmaster_name' => 'Dr. H. Ahmad Fauzi, M.Pd.',
            'treasurer_name' => 'Dra. Siti Aminah, S.Pd.',
        ]);
    }
}
