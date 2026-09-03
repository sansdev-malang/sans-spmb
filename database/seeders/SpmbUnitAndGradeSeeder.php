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
            ['spmb_unit_code' => 'PAUD', 'name' => 'KB', 'is_active' => 1],
            ['spmb_unit_code' => 'PAUD', 'name' => 'TK A', 'is_active' => 1],
            ['spmb_unit_code' => 'PAUD', 'name' => 'TK B', 'is_active' => 1],
            ['spmb_unit_code' => 'SD', 'name' => 'Kelas 1', 'is_active' => 1],
            ['spmb_unit_code' => 'SMP', 'name' => 'Kelas 7', 'is_active' => 1],
        ];

        foreach ($grades as $grade) {
            $unit = SpmbUnit::where('code', $grade['spmb_unit_code'])->first();
            if ($unit) {
                SpmbGrade::updateOrCreate(
                    ['spmb_unit_id' => $unit->id, 'name' => $grade['name']],
                    ['is_active' => $grade['is_active']]
                );
            }
        }
    }
}
