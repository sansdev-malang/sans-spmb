<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SpmbUnit;
use App\Models\SpmbGrade;

class SpmbUnitAndGradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Units
        $units = [
            [
                'name' => 'PAUD Terpadu Anak Saleh',
                'code' => 'PAUD',
                'whatsapp_number' => null,
                'admin_contact_name' => null,
                'is_active' => 1,
                're_registration_instructions_unpaid' => '<ul><li><strong>Pembayaran Fleksibel:</strong> Anda dapat mencentang satu atau beberapa komponen biaya di atas untuk diangsur/dilunasi terlebih dahulu sesuai kelonggaran finansial Anda.</li><li><strong>Batas Pelunasan:</strong> Seluruh biaya administrasi wajib dilunasi sepenuhnya sebelum tahun ajaran baru dimulai.</li><li><strong>Metode Pembayaran:</strong> Klik tombol <strong>Lanjut Bayar</strong> di bawah untuk memilih metode transfer Virtual Account Bank atau pemindaian kode QRIS secara instan.</li></ul>',
                're_registration_instructions_completed' => '<ul><li><strong>Status Resmi:</strong> Selamat, ananda telah resmi menjadi bagian dari keluarga besar Sekolah Anak Saleh.</li><li><strong>Surat Keputusan Penerimaan (SKP):</strong> Anda dapat mengunduh dan mencetak surat kelulusan resmi menggunakan tombol cetak di bawah ini.</li><li><strong>Bukti Pembayaran:</strong> Silakan simpan / cetak kwitansi lunas elektronik sebagai tanda bukti setoran awal Anda yang sah.</li></ul>',
            ],
            [
                'name' => 'Sekolah Dasar Anak Saleh',
                'code' => 'SD',
                'whatsapp_number' => null,
                'admin_contact_name' => null,
                'is_active' => 1,
                're_registration_instructions_unpaid' => '<ul><li><strong>Pembayaran Fleksibel:</strong> Anda dapat mencentang satu atau beberapa komponen biaya di atas untuk diangsur/dilunasi terlebih dahulu sesuai kelonggaran finansial Anda.</li><li><strong>Batas Pelunasan:</strong> Seluruh biaya administrasi wajib dilunasi sepenuhnya sebelum tahun ajaran baru dimulai.</li><li><strong>Metode Pembayaran:</strong> Klik tombol <strong>Lanjut Bayar</strong> di bawah untuk memilih metode transfer Virtual Account Bank atau pemindaian kode QRIS secara instan.</li></ul>',
                're_registration_instructions_completed' => '<ul><li><strong>Status Resmi:</strong> Selamat, ananda telah resmi menjadi bagian dari keluarga besar Sekolah Anak Saleh.</li><li><strong>Surat Keputusan Penerimaan (SKP):</strong> Anda dapat mengunduh dan mencetak surat kelulusan resmi menggunakan tombol cetak di bawah ini.</li><li><strong>Bukti Pembayaran:</strong> Silakan simpan / cetak kwitansi lunas elektronik sebagai tanda bukti setoran awal Anda yang sah.</li></ul>',
            ],
            [
                'name' => 'Sekolah Menengah Pertama Anak Saleh',
                'code' => 'SMP',
                'whatsapp_number' => null,
                'admin_contact_name' => null,
                'is_active' => 1,
                're_registration_instructions_unpaid' => '<ul><li><strong>Pembayaran Fleksibel:</strong> Anda dapat mencentang satu atau beberapa komponen biaya di atas untuk diangsur/dilunasi terlebih dahulu sesuai kelonggaran finansial Anda.</li><li><strong>Batas Pelunasan:</strong> Seluruh biaya administrasi wajib dilunasi sepenuhnya sebelum tahun ajaran baru dimulai.</li><li><strong>Metode Pembayaran:</strong> Klik tombol <strong>Lanjut Bayar</strong> di bawah untuk memilih metode transfer Virtual Account Bank atau pemindaian kode QRIS secara instan.</li></ul>',
                're_registration_instructions_completed' => '<ul><li><strong>Status Resmi:</strong> Selamat, ananda telah resmi menjadi bagian dari keluarga besar Sekolah Anak Saleh.</li><li><strong>Surat Keputusan Penerimaan (SKP):</strong> Anda dapat mengunduh dan mencetak surat kelulusan resmi menggunakan tombol cetak di bawah ini.</li><li><strong>Bukti Pembayaran:</strong> Silakan simpan / cetak kwitansi lunas elektronik sebagai tanda bukti setoran awal Anda yang sah.</li></ul>',
            ],
        ];

        foreach ($units as $unit) {
            SpmbUnit::updateOrCreate(['code' => $unit['code']], $unit);
        }

        // 2. Grades
        $grades = [
            ['spmb_unit_code' => 'PAUD', 'name' => 'TPA 1 (Khusus Guru/Karyawan)', 'min_age_years' => 0, 'min_age_months' => 0, 'max_age_years' => 2, 'max_age_months' => 0, 'age_notes' => 'Usia 0–2 tahun', 'is_active' => 1],
            ['spmb_unit_code' => 'PAUD', 'name' => 'TPA 1 (Umum / 1-2 Tahun)', 'min_age_years' => 1, 'min_age_months' => 0, 'max_age_years' => 2, 'max_age_months' => 0, 'age_notes' => 'Usia 1–2 tahun', 'is_active' => 1],
            ['spmb_unit_code' => 'PAUD', 'name' => 'KB A', 'min_age_years' => 2, 'min_age_months' => 0, 'max_age_years' => 3, 'max_age_months' => 0, 'age_notes' => 'Usia 2–3 tahun', 'is_active' => 1],
            ['spmb_unit_code' => 'PAUD', 'name' => 'KB B', 'min_age_years' => 3, 'min_age_months' => 0, 'max_age_years' => 4, 'max_age_months' => 0, 'age_notes' => 'Usia 3–4 tahun', 'is_active' => 1],
            ['spmb_unit_code' => 'PAUD', 'name' => 'TK A', 'min_age_years' => 4, 'min_age_months' => 0, 'max_age_years' => 5, 'max_age_months' => 11, 'age_notes' => 'Usia 4–5 tahun per 1 Juli', 'is_active' => 1],
            ['spmb_unit_code' => 'PAUD', 'name' => 'TK B', 'min_age_years' => 5, 'min_age_months' => 0, 'max_age_years' => 6, 'max_age_months' => 11, 'age_notes' => 'Usia 5–6 tahun per 1 Juli', 'is_active' => 1],
            ['spmb_unit_code' => 'SD', 'name' => 'Kelas 1', 'min_age_years' => 6, 'min_age_months' => 0, 'max_age_years' => 8, 'max_age_months' => 0, 'age_notes' => 'Usia minimal 6 tahun per 1 Juli', 'is_active' => 1],
            ['spmb_unit_code' => 'SD', 'name' => 'Kelas 2', 'min_age_years' => null, 'min_age_months' => 0, 'max_age_years' => null, 'max_age_months' => 0, 'age_notes' => null, 'is_active' => 1],
            ['spmb_unit_code' => 'SD', 'name' => 'Kelas 3', 'min_age_years' => null, 'min_age_months' => 0, 'max_age_years' => null, 'max_age_months' => 0, 'age_notes' => null, 'is_active' => 1],
            ['spmb_unit_code' => 'SD', 'name' => 'Kelas 4', 'min_age_years' => null, 'min_age_months' => 0, 'max_age_years' => null, 'max_age_months' => 0, 'age_notes' => null, 'is_active' => 1],
            ['spmb_unit_code' => 'SD', 'name' => 'Kelas 5', 'min_age_years' => null, 'min_age_months' => 0, 'max_age_years' => null, 'max_age_months' => 0, 'age_notes' => null, 'is_active' => 1],
            ['spmb_unit_code' => 'SD', 'name' => 'Kelas 6', 'min_age_years' => null, 'min_age_months' => 0, 'max_age_years' => null, 'max_age_months' => 0, 'age_notes' => null, 'is_active' => 1],
            ['spmb_unit_code' => 'SMP', 'name' => 'Kelas 7', 'min_age_years' => 12, 'min_age_months' => 0, 'max_age_years' => 15, 'max_age_months' => 0, 'age_notes' => 'Lulusan SD/MI', 'is_active' => 1],
            ['spmb_unit_code' => 'SMP', 'name' => 'Kelas 8', 'min_age_years' => null, 'min_age_months' => 0, 'max_age_years' => null, 'max_age_months' => 0, 'age_notes' => null, 'is_active' => 1],
            ['spmb_unit_code' => 'SMP', 'name' => 'Kelas 9', 'min_age_years' => null, 'min_age_months' => 0, 'max_age_years' => null, 'max_age_months' => 0, 'age_notes' => null, 'is_active' => 1],
        ];

        foreach ($grades as $grade) {
            $unit = SpmbUnit::where('code', $grade['spmb_unit_code'])->first();
            if ($unit) {
                SpmbGrade::updateOrCreate(
                    ['spmb_unit_id' => $unit->id, 'name' => $grade['name']],
                    [
                        'min_age_years' => $grade['min_age_years'] ?? null,
                        'min_age_months' => $grade['min_age_months'] ?? 0,
                        'max_age_years' => $grade['max_age_years'] ?? null,
                        'max_age_months' => $grade['max_age_months'] ?? 0,
                        'age_notes' => $grade['age_notes'] ?? null,
                        'is_active' => $grade['is_active'],
                    ]
                );
            }
        }
    }
}
