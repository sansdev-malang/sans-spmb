<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SpmbClassProgram;
use App\Models\SpmbExtraService;

class SpmbClassProgramAndServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Class Programs
        $classPrograms = [
            [
                'id' => 1,
                'name' => 'Reguler',
                'description' => 'Program kelas reguler umum',
                'is_active' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Inklusi',
                'description' => 'Program kelas dengan layanan khusus inklusif',
                'is_active' => 1,
            ],
        ];

        foreach ($classPrograms as $cp) {
            SpmbClassProgram::updateOrCreate(['name' => $cp['name']], [
                'description' => $cp['description'],
                'is_active' => $cp['is_active'],
            ]);
        }

        // 2. Extra Services
        $extraServices = [
            [
                'name' => 'Taman Penitipan Anak',
                'code' => 'TPA',
                'is_active' => 1,
            ],
            [
                'name' => "Taman Pendidikan Al-Qur'an",
                'code' => 'TPQ',
                'is_active' => 1,
            ],
        ];

        foreach ($extraServices as $es) {
            SpmbExtraService::updateOrCreate(['code' => $es['code']], [
                'name' => $es['name'],
                'is_active' => $es['is_active'],
            ]);
        }
    }
}
