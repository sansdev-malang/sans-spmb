<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SpmbPeriod;
use App\Models\SpmbWave;
use App\Models\SpmbType;

class SpmbPeriodWaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Periods
        $periods = [
            ['year' => '2027-2028', 'is_active' => 1],
            ['year' => '2029-2030', 'is_active' => 0],
        ];
        foreach ($periods as $period) {
            SpmbPeriod::updateOrCreate(['year' => $period['year']], [
                'is_active' => $period['is_active'],
            ]);
        }

        // 2. Waves
        $waves = [
            [
                'name' => 'Indent',
                'description' => 'Pendaftaran inden sebelum gelombang resmi dibuka',
                'is_active' => 1,
            ],
            [
                'name' => 'Gelombang 1',
                'description' => 'Pendaftaran gelombang pertama reguler',
                'is_active' => 1,
            ],
            [
                'name' => 'Gelombang 2',
                'description' => 'Pendaftaran gelombang kedua reguler',
                'is_active' => 1,
            ],
        ];
        foreach ($waves as $wave) {
            SpmbWave::updateOrCreate(['name' => $wave['name']], [
                'description' => $wave['description'],
                'is_active' => $wave['is_active'],
            ]);
        }

        // 3. Types / Jalur Pendaftaran
        $types = [
            [
                'name' => 'Siswa Baru (Reguler)',
                'description' => 'Jalur umum pendaftaran bagi calon siswa baru',
                'is_active' => 1,
            ],
            [
                'name' => 'Mutasi Masuk / Pindahan',
                'description' => 'Jalur pendaftaran bagi siswa mutasi atau pindahan dari sekolah lain',
                'is_active' => 1,
            ],
            [
                'name' => 'Lanjutan / Internal',
                'description' => 'Jalur khusus bagi lulusan/alumni internal sekolah Anak Saleh yang melanjutkan pendidikan ke jenjang berikutnya.',
                'is_active' => 1,
            ],
        ];
        foreach ($types as $type) {
            SpmbType::updateOrCreate(['name' => $type['name']], [
                'description' => $type['description'],
                'is_active' => $type['is_active'],
            ]);
        }
    }
}
