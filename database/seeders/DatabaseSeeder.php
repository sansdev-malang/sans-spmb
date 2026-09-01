<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. SpmbUnit
        $units = [
            [
                'id' => 1,
                'name' => 'PAUD Terpadu Anak Saleh',
                'code' => 'PAUD',
                'is_active' => 1,
                're_registration_instructions_unpaid' => '<ul><li><strong>Pembayaran Fleksibel:</strong> Anda dapat mencentang satu atau beberapa komponen biaya di atas untuk diangsur/dilunasi terlebih dahulu sesuai kelonggaran finansial Anda.</li><li><strong>Batas Pelunasan:</strong> Seluruh biaya administrasi wajib dilunasi sepenuhnya sebelum tahun ajaran baru dimulai.</li><li><strong>Metode Pembayaran:</strong> Klik tombol <strong>Lanjut ke Pembayaran Online</strong> di bawah untuk memilih metode transfer Virtual Account Bank (BNI) atau pemindaian kode QRIS secara instan.</li><li><strong>Daftar Ulang Resmi:</strong> Setelah seluruh komponen biaya di atas terkonfirmasi <strong>Lunas</strong> oleh sistem, calon siswa secara resmi terdaftar dan Anda dapat mencetak Surat Keterangan Penerimaan (SKP) langsung dari halaman ini.</li></ul>',
                're_registration_instructions_completed' => '<ul><li><strong>Status Resmi:</strong> Selamat, ananda telah resmi menjadi bagian dari keluarga besar Sekolah Anak Saleh.</li><li><strong>Surat Keputusan Penerimaan (SKP):</strong> Anda dapat mengunduh dan mencetak surat kelulusan resmi menggunakan tombol cetak di bawah ini.</li><li><strong>Bukti Pembayaran:</strong> Silakan simpan / cetak kwitansi lunas elektronik sebagai tanda bukti setoran awal Anda yang sah.</li></ul>',
            ],
            [
                'id' => 2,
                'name' => 'Sekolah Dasar Anak Saleh',
                'code' => 'SD',
                'is_active' => 1,
                're_registration_instructions_unpaid' => '<ul><li><strong>Pembayaran Fleksibel:</strong> Anda dapat mencentang satu atau beberapa komponen biaya di atas untuk diangsur/dilunasi terlebih dahulu sesuai kelonggaran finansial Anda.</li><li><strong>Batas Pelunasan:</strong> Seluruh biaya administrasi wajib dilunasi sepenuhnya sebelum tahun ajaran baru dimulai.</li><li><strong>Metode Pembayaran:</strong> Klik tombol <strong>Lanjut ke Pembayaran Online</strong> di bawah untuk memilih metode transfer Virtual Account Bank (BNI) atau pemindaian kode QRIS secara instan.</li><li><strong>Daftar Ulang Resmi:</strong> Setelah seluruh komponen biaya di atas terkonfirmasi <strong>Lunas</strong> oleh sistem, calon siswa secara resmi terdaftar dan Anda dapat mencetak Surat Keterangan Penerimaan (SKP) langsung dari halaman ini.</li></ul>',
                're_registration_instructions_completed' => '<ul><li><strong>Status Resmi:</strong> Selamat, ananda telah resmi menjadi bagian dari keluarga besar Sekolah Anak Saleh.</li><li><strong>Surat Keputusan Penerimaan (SKP):</strong> Anda dapat mengunduh dan mencetak surat kelulusan resmi menggunakan tombol cetak di bawah ini.</li><li><strong>Bukti Pembayaran:</strong> Silakan simpan / cetak kwitansi lunas elektronik sebagai tanda bukti setoran awal Anda yang sah.</li></ul>',
            ],
            [
                'id' => 3,
                'name' => 'Sekolah Menengah Pertama Anak Saleh',
                'code' => 'SMP',
                'is_active' => 1,
                're_registration_instructions_unpaid' => '<ul><li><strong>Pembayaran Fleksibel:</strong> Anda dapat mencentang satu atau beberapa komponen biaya di atas untuk diangsur/dilunasi terlebih dahulu sesuai kelonggaran finansial Anda.</li><li><strong>Batas Pelunasan:</strong> Seluruh biaya administrasi wajib dilunasi sepenuhnya sebelum tahun ajaran baru dimulai.</li><li><strong>Metode Pembayaran:</strong> Klik tombol <strong>Lanjut ke Pembayaran Online</strong> di bawah untuk memilih metode transfer Virtual Account Bank (BNI) atau pemindaian kode QRIS secara instan.</li><li><strong>Daftar Ulang Resmi:</strong> Setelah seluruh komponen biaya di atas terkonfirmasi <strong>Lunas</strong> oleh sistem, calon siswa secara resmi terdaftar dan Anda dapat mencetak Surat Keterangan Penerimaan (SKP) langsung dari halaman ini.</li></ul>',
                're_registration_instructions_completed' => '<ul><li><strong>Status Resmi:</strong> Selamat, ananda telah resmi menjadi bagian dari keluarga besar Sekolah Anak Saleh.</li><li><strong>Surat Keputusan Penerimaan (SKP):</strong> Anda dapat mengunduh dan mencetak surat kelulusan resmi menggunakan tombol cetak di bawah ini.</li><li><strong>Bukti Pembayaran:</strong> Silakan simpan / cetak kwitansi lunas elektronik sebagai tanda bukti setoran awal Anda yang sah.</li></ul>',
            ],
        ];
        foreach ($units as $unit) {
            \App\Models\SpmbUnit::updateOrCreate(['id' => $unit['id']], $unit);
        }

        // 2. SpmbGrade
        $grades = [
            ['id' => 1, 'spmb_unit_id' => 1, 'name' => 'KB', 'is_active' => 1],
            ['id' => 2, 'spmb_unit_id' => 1, 'name' => 'TK A', 'is_active' => 1],
            ['id' => 3, 'spmb_unit_id' => 1, 'name' => 'TK B', 'is_active' => 1],
            ['id' => 4, 'spmb_unit_id' => 2, 'name' => 'Kelas 1', 'is_active' => 1],
            ['id' => 5, 'spmb_unit_id' => 3, 'name' => 'Kelas 7', 'is_active' => 1],
        ];
        foreach ($grades as $grade) {
            \App\Models\SpmbGrade::updateOrCreate(['id' => $grade['id']], $grade);
        }

        // 3. SpmbClassProgram
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
            \App\Models\SpmbClassProgram::updateOrCreate(['id' => $cp['id']], $cp);
        }

        // 4. SpmbExtraService
        $extraServices = [
            [
                'id' => 1,
                'name' => 'Taman Penitipan Anak',
                'code' => 'TPA',
                'is_active' => 1,
            ],
            [
                'id' => 2,
                'name' => "Taman Pendidikan Al-Qur'an",
                'code' => 'TPQ',
                'is_active' => 1,
            ],
        ];
        foreach ($extraServices as $es) {
            \App\Models\SpmbExtraService::updateOrCreate(['id' => $es['id']], $es);
        }

        // 5. SpmbPeriod
        $periods = [
            ['id' => 1, 'year' => '2027-2028', 'is_active' => 1],
            ['id' => 2, 'year' => '2029-2030', 'is_active' => 0],
        ];
        foreach ($periods as $period) {
            \App\Models\SpmbPeriod::updateOrCreate(['id' => $period['id']], $period);
        }

        // 6. SpmbWave
        $waves = [
            [
                'id' => 1,
                'name' => 'Indent',
                'description' => 'Pendaftaran inden sebelum gelombang resmi dibuka',
                'is_active' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Gelombang 1',
                'description' => 'Pendaftaran gelombang pertama reguler',
                'is_active' => 1,
            ],
            [
                'id' => 3,
                'name' => 'Gelombang 2',
                'description' => 'Pendaftaran gelombang kedua reguler',
                'is_active' => 0,
            ],
        ];
        foreach ($waves as $wave) {
            \App\Models\SpmbWave::updateOrCreate(['id' => $wave['id']], $wave);
        }

        // 7. SpmbType
        $types = [
            [
                'id' => 1,
                'name' => 'Siswa Baru (Reguler)',
                'description' => 'Jalur umum pendaftaran bagi calon siswa baru',
                'is_active' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Mutasi Masuk / Pindahan',
                'description' => 'Jalur pendaftaran bagi siswa mutasi atau pindahan dari sekolah lain',
                'is_active' => 0,
            ],
            [
                'id' => 3,
                'name' => 'Lanjutan / Internal',
                'description' => 'Jalur khusus bagi lulusan/alumni internal sekolah Anak Saleh yang melanjutkan pendidikan ke jenjang berikutnya.',
                'is_active' => 0,
            ],
        ];
        foreach ($types as $type) {
            \App\Models\SpmbType::updateOrCreate(['id' => $type['id']], $type);
        }

        // 8. SpmbFeeCategory
        $feeCategories = [
            ['id' => 1, 'name' => 'Formulir Pendaftaran'],
            ['id' => 2, 'name' => 'Biaya Administrasi'],
            ['id' => 3, 'name' => 'Biaya Tambahan'],
        ];
        foreach ($feeCategories as $category) {
            \App\Models\SpmbFeeCategory::updateOrCreate(['id' => $category['id']], $category);
        }

        // 9. SpmbFee
        $fees = [
            [
                'id' => 1,
                'spmb_fee_category_id' => 1,
                'spmb_unit_id' => 1,
                'name' => 'Formulir Pendaftaran PAUD',
                'amount' => 350000,
                'payment_gateway' => 'winpay',
                'is_active' => 1,
            ],
            [
                'id' => 2,
                'spmb_fee_category_id' => 1,
                'spmb_unit_id' => 2,
                'name' => 'Formulir Pendaftaran SD',
                'amount' => 350000,
                'payment_gateway' => 'winpay',
                'is_active' => 0,
            ],
            [
                'id' => 3,
                'spmb_fee_category_id' => 2,
                'spmb_unit_id' => 1,
                'name' => "Uang Gedung (Musa'adah)",
                'amount' => 3500000,
                'payment_gateway' => 'winpay',
                'is_active' => 1,
            ],
            [
                'id' => 4,
                'spmb_fee_category_id' => 2,
                'spmb_unit_id' => 1,
                'name' => 'Biaya Seragam',
                'amount' => 1200000,
                'payment_gateway' => 'winpay',
                'is_active' => 1,
            ],
            [
                'id' => 5,
                'spmb_fee_category_id' => 2,
                'spmb_unit_id' => 2,
                'name' => "Uang Gedung (Musa'adah)",
                'amount' => 13000000,
                'payment_gateway' => 'winpay',
                'is_active' => 1,
            ],
            [
                'id' => 6,
                'spmb_fee_category_id' => 2,
                'spmb_unit_id' => 2,
                'name' => 'Biaya Seragam',
                'amount' => 1800000,
                'payment_gateway' => 'winpay',
                'is_active' => 1,
            ],
            [
                'id' => 7,
                'spmb_fee_category_id' => 1,
                'spmb_unit_id' => 3,
                'name' => 'Formulir Pendaftaran SMP',
                'amount' => 350000,
                'payment_gateway' => 'winpay',
                'is_active' => 0,
            ],
            [
                'id' => 8,
                'spmb_fee_category_id' => 2,
                'spmb_unit_id' => 2,
                'name' => 'Biaya Buku Cambridge',
                'amount' => 1400000,
                'payment_gateway' => 'winpay',
                'is_active' => 0,
            ],
            [
                'id' => 9,
                'spmb_fee_category_id' => 2,
                'spmb_unit_id' => 3,
                'name' => "Uang Gedung (Musa'adah)",
                'amount' => 15000000,
                'payment_gateway' => 'winpay',
                'is_active' => 0,
            ],
            [
                'id' => 10,
                'spmb_fee_category_id' => 2,
                'spmb_unit_id' => 3,
                'name' => 'Biaya Seragam',
                'amount' => 1500000,
                'payment_gateway' => 'winpay',
                'is_active' => 0,
            ],
            [
                'id' => 11,
                'spmb_fee_category_id' => 3,
                'spmb_unit_id' => 1,
                'name' => 'TPA',
                'amount' => 1400000,
                'payment_gateway' => 'winpay',
                'is_active' => 0,
            ],
            [
                'id' => 12,
                'spmb_fee_category_id' => 3,
                'spmb_unit_id' => 1,
                'name' => 'TPQ',
                'amount' => 1500000,
                'payment_gateway' => 'winpay',
                'is_active' => 0,
            ],
        ];
        foreach ($fees as $fee) {
            $fee['payment_gateway'] = is_array($fee['payment_gateway']) ? $fee['payment_gateway'] : [$fee['payment_gateway']];
            \App\Models\SpmbFee::updateOrCreate(['id' => $fee['id']], $fee);
        }

        // 10. Pivot spmb_fee_category_unit
        if (Schema::hasTable('spmb_fee_category_unit')) {
            DB::table('spmb_fee_category_unit')->truncate();
            $pivot = [
                ['spmb_fee_category_id' => 3, 'spmb_unit_id' => 1],
                ['spmb_fee_category_id' => 3, 'spmb_unit_id' => 2],
                ['spmb_fee_category_id' => 3, 'spmb_unit_id' => 3],
                ['spmb_fee_category_id' => 2, 'spmb_unit_id' => 1],
                ['spmb_fee_category_id' => 2, 'spmb_unit_id' => 2],
                ['spmb_fee_category_id' => 2, 'spmb_unit_id' => 3],
                ['spmb_fee_category_id' => 1, 'spmb_unit_id' => 1],
                ['spmb_fee_category_id' => 1, 'spmb_unit_id' => 2],
                ['spmb_fee_category_id' => 1, 'spmb_unit_id' => 3],
            ];
            DB::table('spmb_fee_category_unit')->insert($pivot);
        }

        // 11. Settings
        $settings = [
            ['key' => 'winpay_mode', 'value' => 'simulator'],
            ['key' => 'winpay_merchant_id', 'value' => 'MOCK_MERCHANT_ID'],
            ['key' => 'winpay_client_key', 'value' => 'MOCK_CLIENT_KEY'],
            ['key' => 'winpay_client_secret', 'value' => 'MOCK_CLIENT_SECRET'],
            ['key' => 'winpay_sandbox_merchant_id', 'value' => '171001519'],
            ['key' => 'winpay_sandbox_client_key', 'value' => 'f754edbc-dc93-49e6-986a-1e9e18158938'],
            ['key' => 'winpay_sandbox_client_secret', 'value' => 'SANDBOX_CLIENT_SECRET'],
            ['key' => 'winpay_sandbox_private_key', 'value' => "-----BEGIN PRIVATE KEY-----\n" .
                "MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDQHqtVMsLLWxY8\n" .
                "7hPUHbPxzmniCUjjp8fctjPvqOH1CZZWTeBKN4ag0jiFy0/wYhFvEYS4JhdTuNoc\n" .
                "vuJmffHKJuvMCu3C6g7FxlBYobtQWKmBng1lVJ1Iyz6NrewNVXANmfycFdXuCH6M\n" .
                "RQ6vudS5H5QCdWtu3pBkkaHSJaWzy0B6KUKfKT25kJ9Ncsv2tjT1H3NlqkxQvp8Y\n" .
                "cYsHMiL15jPnP1v+pa82O6u/WBXFxKJ1DaybG8dy60cuDEpN6M1DwrziemSmBRrw\n" .
                "b1I4LOe5duBR3r6JGW3qPkgtFp2O/PXGRJHBfd8CxJzhTLrSF+1prBJ454oywq92\n" .
                "zqXESRnhAgMBAAECggEAVicUAtlYBOmIe6GMiMbg+izZ7Q2t5DvMwwOT3VZ6bz7Q\n" .
                "QprLSb3Rl9peNpiS124pTGKin7548pn3hGXKf+YMBQR2oQk3InRUuC9fjEkrKtgB\n" .
                "F1yPrA5Ka9ti4jCIon5nO+IuTYjGfdp7VGKz8S+KrTWyxg/IcOVmPZOBuuYFwbaW\n" .
                "tPb6Fil3beijrPzB0yrP0hF82CxqbLY3Tg0BOZiXrFNFjmyckcfTERYIaUBhTuAX\n" .
                "sPceBX2bGt6Wu50Agv0LbDc66jIZvsS11hoKu1TSo8yOkvZoL+9LKOW84pCFJDX0\n" .
                "6U70Gwy7wfBpdKf+WOCs/Fr/zQKbgVujfLMA06JUZwKBgQD4XvoG1dc4pwbyrfs1\n" .
                "xxBmM7B0ASxSqHJ448wvKEAyb1ME6wKTBXGjDNYbdFLVX+vpTEtHeSHkMu+hR1yF\n" .
                "jYsRfltSu/vJT2z3VPd/3h2iDx5owwcGcPP45Ad/iLhmIl+0gbaxMkBpqJCF+rRs\n" .
                "+iKn5soxNHwP1YbhRuPDo9kZZwKBgQDWgy8hFwvKNKnKKWzjREFOqPZpt+flWXYR\n" .
                "yuaUVhaq4eYzJ33TA71nEP+ZvtaEoisnHAqHxtvXhCc1m7Cj5jlHCHwQiQcG3YEf\n" .
                "hyTBlbcGXIJQKpGdsq760F8zfrfpGy3ClrZCo7ywViiQaQ0bCRQAevOLB4/RFVoR\n" .
                "n+qHefF9dwKBgQDsSv+4LQ27Gj0j+J38xcw2T4racptGcHenx6FkY/jfgsYK8cLb\n" .
                "ONyp8PZp3DtKQR3iMPGVqAq0XjlYyNmfPdBG7l3X0nxzQ5s5m550Ck9K9PNLW/B9\n" .
                "Ek0qR1dS4DH/CUjgJGA5KMPbQcFtldy9qSP7dTh7o6E8NztBa/4ZDPLolQKBgQDB\n" .
                "tVLg0bvezDGrEj929xL2YlOqYd0x2dhp9szDlP4BL989wGK6I71sjggSoSd8PCk1\n" .
                "tve3ZpbthjQWD9KyHtsITxwhnmvPAkVw4AwMGBNf1jgDBn3aZxnl+jaN/Nc81EM9\n" .
                "XfWWNd/VaOhWh9bC3C7IxD6bBKgVSe+8zKjvz+mHvwKBgB61mcNFAGd0EJPKDMbN\n" .
                "BhlXjwUv81EJ37quz26sLFm2SOMIm09zB/TIbWbqpGWjV46LWYZgePbhl2XhHtGP\n" .
                "TTDX5e5Qi6zAtUOOF1rPV9B8sWTmyLDkqOMGurIoLV0goItbLIbj1usf/sAmCHIz\n" .
                "HXAZEuYXVTbkUdedO9K0jqRc\n" .
                "-----END PRIVATE KEY-----"],
        ];
        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(['key' => $setting['key']], ['value' => $setting['value']]);
        }

        // 12. SpmbFormStep
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\Models\SpmbFormStep::truncate();
        \App\Models\SpmbFormField::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $formSteps = [
            ['id' => 1, 'title' => 'Pilihan Program & Layanan', 'order' => 1, 'is_active' => 1],
            ['id' => 2, 'title' => 'Informasi Calon Siswa', 'order' => 2, 'is_active' => 1],
            ['id' => 3, 'title' => 'Data Orang Tua / Wali', 'order' => 3, 'is_active' => 1],
            ['id' => 4, 'title' => 'Dokumen Persyaratan', 'order' => 4, 'is_active' => 1],
        ];
        foreach ($formSteps as $step) {
            \App\Models\SpmbFormStep::updateOrCreate(['id' => $step['id']], $step);
        }

        // 13. SpmbFormField
        $formFields = [
            [
                'id' => 15,
                'form_step_id' => 1,
                'label' => 'Tahun Ajaran',
                'field_name' => 'spmb_period_id',
                'type' => 'select',
                'options' => null,
                'is_required' => 1,
                'order' => 1,
            ],
            [
                'id' => 16,
                'form_step_id' => 1,
                'label' => 'Gelombang Pendaftaran',
                'field_name' => 'spmb_wave_id',
                'type' => 'select',
                'options' => null,
                'is_required' => 1,
                'order' => 2,
            ],
            [
                'id' => 17,
                'form_step_id' => 1,
                'label' => 'Jalur Pendaftaran',
                'field_name' => 'spmb_type_id',
                'type' => 'select',
                'options' => null,
                'is_required' => 1,
                'order' => 3,
            ],
            [
                'id' => 18,
                'form_step_id' => 1,
                'label' => 'Program Kelas',
                'field_name' => 'spmb_class_program_id',
                'type' => 'select',
                'options' => null,
                'is_required' => 1,
                'order' => 4,
            ],
            [
                'id' => 1,
                'form_step_id' => 2,
                'label' => 'Nama Lengkap (Sesuai Akte)',
                'field_name' => 'candidate_name',
                'type' => 'text',
                'options' => null,
                'is_required' => 1,
                'order' => 1,
            ],
            [
                'id' => 2,
                'form_step_id' => 2,
                'label' => 'Nama Panggilan',
                'field_name' => 'nickname',
                'type' => 'text',
                'options' => null,
                'is_required' => 0,
                'order' => 2,
            ],
            [
                'id' => 3,
                'form_step_id' => 2,
                'label' => 'NIK (Nomor Induk Kependudukan)',
                'field_name' => 'nik',
                'type' => 'number',
                'options' => null,
                'is_required' => 0,
                'order' => 3,
            ],
            [
                'id' => 4,
                'form_step_id' => 2,
                'label' => 'Jenis Kelamin',
                'field_name' => 'gender',
                'type' => 'select',
                'options' => 'Laki-laki,Perempuan',
                'is_required' => 1,
                'order' => 4,
            ],
            [
                'id' => 5,
                'form_step_id' => 2,
                'label' => 'Agama',
                'field_name' => 'religion',
                'type' => 'select',
                'options' => 'Islam,Kristen,Katolik,Hindu,Budha,Konghucu',
                'is_required' => 1,
                'order' => 5,
            ],
            [
                'id' => 6,
                'form_step_id' => 2,
                'label' => 'Tempat Lahir',
                'field_name' => 'birth_place',
                'type' => 'text',
                'options' => null,
                'is_required' => 1,
                'order' => 6,
            ],
            [
                'id' => 7,
                'form_step_id' => 2,
                'label' => 'Tanggal Lahir',
                'field_name' => 'birth_date',
                'type' => 'date',
                'options' => null,
                'is_required' => 1,
                'order' => 7,
            ],
            [
                'id' => 8,
                'form_step_id' => 2,
                'label' => 'Asal Sekolah (TK/RA/PAUD)',
                'field_name' => 'previous_school',
                'type' => 'text',
                'options' => null,
                'is_required' => 0,
                'order' => 8,
            ],

            [
                'id' => 10,
                'form_step_id' => 3,
                'label' => 'Nama Ayah Kandung',
                'field_name' => 'father_name',
                'type' => 'text',
                'options' => null,
                'is_required' => 1,
                'order' => 1,
            ],
            [
                'id' => 11,
                'form_step_id' => 3,
                'label' => 'Nama Ibu Kandung',
                'field_name' => 'mother_name',
                'type' => 'text',
                'options' => null,
                'is_required' => 1,
                'order' => 2,
            ],
            [
                'id' => 12,
                'form_step_id' => 3,
                'label' => 'No. HP Wali (WhatsApp)',
                'field_name' => 'parent_phone',
                'type' => 'number',
                'options' => null,
                'is_required' => 1,
                'order' => 3,
            ],
            [
                'id' => 13,
                'form_step_id' => 4,
                'label' => 'Scan Akta Kelahiran',
                'field_name' => 'birth_certificate_path',
                'type' => 'file',
                'options' => null,
                'is_required' => 1,
                'order' => 1,
            ],
            [
                'id' => 14,
                'form_step_id' => 4,
                'label' => 'Scan Kartu Keluarga',
                'field_name' => 'family_card_path',
                'type' => 'file',
                'options' => null,
                'is_required' => 1,
                'order' => 2,
            ],
            [
                'id' => 19,
                'form_step_id' => 1,
                'label' => 'Layanan Non-Formal',
                'field_name' => 'extra_services',
                'type' => 'select',
                'options' => null,
                'is_required' => 0,
                'order' => 10,
            ],
        ];
        foreach ($formFields as $field) {
            \App\Models\SpmbFormField::updateOrCreate(['id' => $field['id']], $field);
        }

        // 14. PaymentGateway
        $gateways = [
            [
                'id' => 1,
                'name' => 'Winpay Gateway',
                'code' => 'winpay',
                'is_active' => true,
                'settings_schema' => [
                    ['key' => 'merchant_id', 'type' => 'text', 'label' => 'Merchant ID'],
                    ['key' => 'client_key', 'type' => 'text', 'label' => 'Client Key'],
                    ['key' => 'client_secret', 'type' => 'password', 'label' => 'Client Secret'],
                    ['key' => 'private_key', 'type' => 'textarea', 'label' => 'Private Key (RSA)'],
                    ['key' => 'public_key', 'type' => 'textarea', 'label' => 'Public Key (RSA)'],
                ],
            ],
            [
                'id' => 2,
                'name' => 'BNI SNAP QRIS MPM',
                'code' => 'bni',
                'is_active' => true,
                'settings_schema' => [
                    ['key' => 'merchant_id', 'type' => 'text', 'label' => 'Merchant ID'],
                    ['key' => 'terminal_id', 'type' => 'text', 'label' => 'Terminal ID (TID)'],
                    ['key' => 'client_id', 'type' => 'text', 'label' => 'Client ID'],
                    ['key' => 'client_secret', 'type' => 'password', 'label' => 'Client Secret'],
                    ['key' => 'private_key', 'type' => 'textarea', 'label' => 'Private Key (RSA)'],
                ],
            ],
        ];
        foreach ($gateways as $gw) {
            \App\Models\PaymentGateway::updateOrCreate(['id' => $gw['id']], [
                'name' => $gw['name'],
                'code' => $gw['code'],
                'is_active' => $gw['is_active'],
                'settings_schema' => $gw['settings_schema'],
            ]);
        }

        // 14b. SpmbPaymentChannel
        $channels = [
            // Winpay Channels (gateway id 1)
            [
                'code' => 'MANDIRI',
                'name' => 'Mandiri Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'BCA',
                'name' => 'BCA Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'BNI',
                'name' => 'BNI Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'BRI',
                'name' => 'BRI Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'PERMATA',
                'name' => 'Permata Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'BSI',
                'name' => 'BSI Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'MUAMALAT',
                'name' => 'Muamalat Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'CIMB',
                'name' => 'CIMB Niaga Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'SINARMAS',
                'name' => 'Sinarmas Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'BNC',
                'name' => 'BNC Virtual Account',
                'type' => 'va',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'QRIS',
                'name' => 'QRIS',
                'type' => 'qris',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'DANA',
                'name' => 'DANA',
                'type' => 'ewallet',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'SHOPEEPAY',
                'name' => 'ShopeePay',
                'type' => 'ewallet',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'INDOMARET',
                'name' => 'Indomaret',
                'type' => 'retail',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            [
                'code' => 'ALFAMART',
                'name' => 'Alfamart',
                'type' => 'retail',
                'is_active' => true,
                'payment_gateway_id' => 1,
            ],
            // BNI SNAP Channels (gateway id 2)
            [
                'code' => 'BNI_QRIS',
                'name' => 'BNI SNAP QRIS',
                'type' => 'qris',
                'is_active' => true,
                'payment_gateway_id' => 2,
            ],
        ];
        foreach ($channels as $chan) {
            \App\Models\SpmbPaymentChannel::updateOrCreate(
                ['code' => $chan['code']],
                $chan
            );
        }

        // 15. Users (password hashed as sans1234)
        $users = [
            [
                'id' => 2,
                'name' => 'Admin Paud',
                'email' => 'admin-paud@sans.dev',
                'password' => Hash::make('sans1234'),
                'role' => 'admin',
                'spmb_unit_id' => 1,
            ],
            [
                'id' => 3,
                'name' => 'TIM DEV',
                'email' => 'superadmin@sans.dev',
                'password' => Hash::make('sans1234'),
                'role' => 'super_admin',
                'spmb_unit_id' => null,
            ],
            [
                'id' => 6,
                'name' => 'Admin SD',
                'email' => 'admin-sd@sans.dev',
                'password' => Hash::make('sans1234'),
                'role' => 'admin',
                'spmb_unit_id' => 2,
            ],
            [
                'id' => 7,
                'name' => 'Admin SMP',
                'email' => 'admin-smp@sans.dev',
                'password' => Hash::make('sans1234'),
                'role' => 'admin',
                'spmb_unit_id' => 3,
            ],
        ];
        foreach ($users as $user) {
            User::updateOrCreate(['id' => $user['id']], $user);
        }

        // 12. SpmbAgreementTemplate
        $agreementBody = '<p>Saya yang bertanda tangan di bawah ini selaku Orang Tua / Wali murid dari calon siswa:</p>
<div class="pl-4 my-2 space-y-1 font-semibold text-slate-800 dark:text-slate-200">
    <p>Nama Calon Siswa : <strong>{{nama_calon_siswa}}</strong></p>
    <p>Unit & Program : <strong>{{nama_unit}} - {{nama_kelas}}</strong></p>
    <p>Tahun Ajaran : <strong>{{tahun_ajaran}}</strong></p>
</div>
<p class="mt-4">Menyatakan dengan sesungguhnya dan penuh kesadaran bahwa:</p>
<ol class="list-decimal pl-5 space-y-3 mt-2 text-slate-700 dark:text-slate-350">
    <li><strong>Kami bersedia dan menyetujui:</strong>
        <ol class="list-[lower-alpha] pl-5 space-y-1.5 mt-1">
            <li>Mematuhi semua peraturan, ketentuan, tata tertib, kebijakan, dan prosedur yang dibuat dan berlaku di Sekolah Anak Saleh, baik yang telah berlaku maupun yang akan diberlakukan di kemudian hari (selama tidak ada pihak yang dirugikan), baik tertulis maupun tidak tertulis, termasuk pada Peraturan Yayasan Pendidikan Anak Saleh, Tata Tertib, Kode Etik, dan lain-lain.</li>
            <li>Menerima visi, misi, tujuan, metode, dan tata kelola Sekolah Anak Saleh dalam mendidik semua murid di Sekolah Anak Saleh dan karenanya kami percaya bahwa segala peraturan dan arah kerja pendidikan yang dibuat oleh Sekolah Anak Saleh adalah untuk kebaikan murid dan pihak sekolah.</li>
            <li>Menerima implementasi, internalisasi, sosialisasi dan kulturisasi Panca Karakter Anak Saleh sebagai basis utama pembelajaran dan pendidikan karakter di lingkungan Sekolah Anak Saleh.</li>
            <li>Semua murid wajib ikut serta dan/atau berpartisipasi dalam semua kegiatan Sekolah Anak Saleh. Kami menerima bahwa ketidakikutsertaan murid dalam kegiatan tersebut dapat berakibat kepada penilaian hasil belajarnya di Sekolah Anak Saleh dan tidak menggugurkan kewajiban kami untuk membayar biaya pendidikan sesuai ketentuan yang berlaku sama bagi seluruh murid yang mengikuti kegiatan Sekolah Anak Saleh.</li>
            <li>Aktif mengikuti, partisipasi, dan mendukung dalam kegiatan Parenting, Pengajian, Bakti Sosial/Sosial Keagamaan dan kegiatan outing (outbound, home visit, moving home, family Inn, field trip, dan kegiatan-kegiatan lainnya) yang diadakan Sekolah Anak Saleh dan/atau bekerjasama dengan Forkel (Forum Kelas) atau Komite Sekolah.</li>
            <li>Menerima dan tidak mempertentangkan ajaran Islam Ahli Sunnah wal Jama’ah yang Rahmatan Lil ‘Alamin yang diajarkan di lingkungan Sekolah Anak Saleh.</li>
            <li>Menerima dan tidak menentang landasan yang digunakan di lingkungan Sekolah Anak Saleh yakni Al-Qur’an, Al-Hadits, Ijma’, dan Qiyas (termasuk di dalamnya hukum-hukum yang sah menurut Undang-Undang dan peraturan lain yang berlaku di Negara Kesatuan Republik Indonesia).</li>
            <li>Tidak membawa kepentingan apapun ke dalam lingkungan Sekolah Anak Saleh baik kepada wali murid lain maupun kepada warga sekolah yang berkaitan dengan politik, ormas, dan kegiatan yang bersifat SARA atau yang dapat berpotensi memecah belah kerukunan.</li>
            <li>Menerima dan mendukung segala bentuk program inklusi di lingkungan Sekolah Anak Saleh serta tidak mempermasalahkan keberadaan Murid Berkebutuhan Khusus (ABK/Special Need) di lingkungan Sekolah Anak Saleh sebagai komitmen bersama education for all.</li>
            <li>Apabila ananda pada saat didaftarkan oleh kami sebagai orangtua masuk jalur reguler (bukan jalur Murid Berkebutuhan Khusus (ABK)) yang selanjutnya dinyatakan diterima, akan tetapi ternyata hasil dari observasi dalam perkembangannya ananda mengalami kendala yang masuk dalam kategori Murid Berkebutuhan Khusus (ABK), maka kami sebagai orangtua siap menyediakan GPK (Guru Pembimbing Khusus) serta tidak mempertentangkan atas keputusan pihak sekolah dalam memutuskan kebutuhan GPK terhadap ananda.</li>
            <li>Memahami dan menyadari jika anak merupakan amanah Allah SWT yang harus dijaga dan dididik oleh orangtua/wali sebagai penerima utama amanah tersebut, sehingga akan berikhtiar dalam bekerjasama dengan Sekolah Anak Saleh untuk memperhatikan dan mengusahakan kesejahteraan fisik dan mental, tumbuh kembang dan pendidikannya dalam semangat cinta dan kasih.</li>
            <li>Tidak melakukan ujaran maupun tindakan provokasi terhadap kebijakan sekolah serta menyampaikan kritik dan saran secara santun sesuai adab ketimuran dan karakter Anak Saleh, kepada pimpinan sekolah, dan tidak mengelaborasi masalah yang dapat menyebabkan disharmonisasi atau polemik diantara sekolah maupun wali murid lainnya.</li>
            <li>Memahami posisi dan kewenangan sebagai wali murid dengan tidak mancampuri urusan yang menjadi ranah dan kewenangan Sekolah Anak Saleh antara lain: kurikulum dan pembelajaran, administrasi dan manajemen sekolah, organisasi dan kelembagaan sekolah, ketenagaan, keuangan, sarana dan prasarana, serta program kegiatan.</li>
            <li>Apabila dikemudian hari terdapat perselisihan dengan pihak sekolah maka bersedia untuk menyelesaikan dengan cara kekeluargaan serta tidak melibatkan pihak luar seperti Lembaga Swadaya Masyarakat (LSM) dan sejenisnya.</li>
            <li>Bersedia mengganti rugi atas kelalaian yang disengaja maupun tidak atas kerusakan fasilitas sekolah yang disebabkan oleh anak kami.</li>
            <li>Apabila terjadi hal-hal yang diluar kendali atau Force majeure akan tetap berkomitmen untuk menyelesaikan segala bentuk kewajiban dengan tidak mempertentangkan kebijakan atau langkah strategis yang diambil oleh Yayasan Pendidikan Anak Saleh demi kebaikan bersama.</li>
            <li>Mengikuti segala hal berdasarkan aturan dan penjelasan resmi dari pimpinan sekolah bukan atas pernyataan sepihak apalagi kabar burung atau yang tidak berdasar dari pihak manapun.</li>
        </ol>
    </li>
    <li class="mt-4"><strong>Kami menyetujui pembiayaan di Sekolah Anak Saleh:</strong>
        <ol class="list-[lower-alpha] pl-5 space-y-1.5 mt-1">
            <li>Bahwa biaya pendidikan (musa’adah, syahriyah, dan pembiayaan lain yang ditentukan Yayasan Pendidikan Anak Saleh) yang harus dipenuhi selama murid mengikuti pendidikan di Sekolah Anak Saleh dengan cara dan waktu pembayaran yang ditetapkan oleh Yayasan menjadi tanggungjawab kami untuk membayarnya secara tepat waktu.</li>
            <li>Bahwa kewajiban pembayaran biaya pendidikan yang terhitung (tunggakan) tidak dapat terhapus meski murid sudah tidak mengikuti pendidikan di Sekolah Anak Saleh. Kami akan tetap menyelesaikan tunggakan sebagai hutang yang harus dibayarkan. Oleh karenanya kami akan menyelesaikan tunggakan tersebut sebelum anak kami tidak lagi mengikuti Pendidikan di Sekolah Anak Saleh.</li>
            <li>Khusus mengenai uang kegiatan murid, kami sepakat besaran uang kegiatan murid adalah bagian tak terpisahkan dari biaya pendidikan yang wajib dibayar secara penuh, baik murid yang bersangkutan ikut ataupun tidak dengan kegiatan dimaksud karena alasan apapun.</li>
            <li>Sekolah Anak Saleh berwenang sepenuhnya untuk mengelola dan menggunakan uang musa’adah, syahriyah, uang kegiatan, uang amal, serta bantuan operasional oleh pemerintah untuk kegiatan murid atau keperluan lain yang dianggap perlu dan baik guna kemajuan Sekolah Anak Saleh.</li>
            <li>Disadari bahwa beban-beban penyelenggaraan pendidikan harus terus-menerus disesuaikan mengikuti inflasi, kenaikan biaya dan harga-harga, kenaikan berkala gaji asatidz (bisyaroh) dan sebagainya. Oleh karena itu kami orangtua dan/wali murid bersedia menerima kenaikan syahriyah (SPP) selama sesuai dengan keadaan dan kebutuhan riil yang berkembang.</li>
            <li>Bila kami belum bisa melunasi kewajiban biaya pendidikan yang harus dibayarkan, maka kami menyadari dan menerima bahwa setiap saat Sekolah Anak Saleh berwenang menunda pemberian hak akademik murid yang bersangkutan, menahan raport, ijazah dan/atau dokumen lainnya sampai dengan kewajiban biaya pendidikan yang harus dibayarkan dapat dilunasi.</li>
            <li>Tidak akan memberikan hadiah/gratifikasi kepada asatidz dan karyawan maupun pimpinan Sekolah Anak Saleh secara perseorangan sehingga pemberian tersebut, disengaja untuk dapat mempengaruhi obyektivitas asatidz dan karyawan maupun pimpinan Sekolah Anak Saleh terhadap murid.</li>
            <li>Seluruh biaya pendaftaran dan pendidikan yang telah dibayarkan, tidak dapat diminta kembali dengan alasan apapun.</li>
        </ol>
    </li>
    <li class="mt-4"><strong>Kami menyetujui bahwa Sekolah Anak Saleh berwenang untuk:</strong>
        <ol class="list-[lower-alpha] pl-5 space-y-1.5 mt-1">
            <li>Memberikan nilai dan keterangan terhadap murid yang akan tertuang di dalam raport oleh asatidz sesuai dengan penilaian-penilaian yang telah diberlakukan.</li>
            <li>Menentukan penempatan kelas murid atas pertimbangan oleh tim asatidz dengan persetujuan kepala sekolah.</li>
            <li>Menentukan murid yang dapat/tidak dapat naik kelas dan yang dapat/tidak dapat melanjutkan pendidikan di Sekolah Anak Saleh.</li>
            <li>Mengambil segala tindakan yang perlu, termasuk memberhentikan murid bila didapati di kemudian hari bahwa orangtua dan/wali murid telah memberikan keterangan yang salah, palsu dan/atau menghilangkan sebagian atau seluruh keterangan tertentu mengenai data-data murid serta dokumen-dokumen pendukungnya.</li>
            <li>Mengambil segala tindakan yang perlu, termasuk memberhentikan murid bila didapati dikemudian hari bahwa orangtua dan/wali murid melakukan tindakan-tindakan merugikan warga sekolah maupun wali murid lainnya seperti provokasi, menyebarkan berita bohong, dan tindakan merugikan lainnya.</li>
            <li>Menindaklanjuti dengan menyesuaikan dengan program inklusi yang ada di Sekolah Anak Saleh ketika dalam proses perkembangannya murid didiagnosa mengalami kebutuhan khusus dengan data-data dan hasil asesmen yang jelas dan terukur dari ahli tumbuh kembang anak.</li>
            <li>Mengambil segala tindakan yang dianggap perlu, termasuk memberi peringatan, melakukan skorsing hingga memberhentikan murid apabila murid kedapatan melakukan tindakan melanggar peraturan sekolah, bullying, perusakan fasilitas, penggunaan obat terlarang, pornografi/pornoaksi, pencemaran nama baik, atau tindakan vandalisme lainnya.</li>
            <li>Mengadakan pengujian klinis atas penggunaan obat-obatan terlarang, baik secara acak maupun menyeluruh, sekolah juga berhak untuk mengambil tindakan tertentu yang dianggap baik untuk kepentingan sekolah secara keseluruhan.</li>
        </ol>
    </li>
    <li class="mt-4"><strong>Kami bertanggung jawab atas urusan antar jemput:</strong>
        <ol class="list-[lower-alpha] pl-5 space-y-1.5 mt-1">
            <li>Bahwa antar jemput murid ke/dari sekolah-rumah merupakan tanggungjawab kami sebagai keluarga.</li>
            <li>Bahwa penggunaan mobil antar-jemput sekolah didasarkan pada unit yang ditunjuk dan direferensikan oleh sekolah. Kami tidak menyalahkan atau meminta tanggungjawab (menuntut) sekolah bilamana terjadi sesuatu hal karena penggunaan mobil antar jemput bukan yang ditunjuk/direferensi sekolah.</li>
            <li>Bahwa pada waktu pulang sekolah, murid wajib dijemput orangtua/wali atau yang mewakili dengan kesepakatan terlebih dahulu antara orangtua/wali dengan sekolah. Wali kelas harus diberitahu jika orang yang menjemput berbeda dari biasanya.</li>
        </ol>
    </li>
</ol>';

        $units = \App\Models\SpmbUnit::all();
        foreach ($units as $unit) {
            \App\Models\SpmbAgreementTemplate::updateOrCreate(
                ['spmb_unit_id' => $unit->id],
                [
                    'title' => 'SURAT PERNYATAAN KESANGGUPAN MEMATUHI PERATURAN & BIAYA PENDIDIKAN YAYASAN PENDIDIKAN ANAK SALEH',
                    'content' => $agreementBody,
                    'rules_consent_label' => 'Saya menyetujui seluruh tata tertib dan peraturan akademik Sekolah Anak Saleh.',
                    'fees_consent_label' => 'Saya menyanggupi pemenuhan seluruh rincian biaya pendidikan dan administrasi masuk yayasan.'
                ]
            );
        }

        // 13. Settings
        $defaultSettings = [
            'school_name' => 'Sekolah Anak Saleh',
            'portal_hero_title' => 'Membangun Generasi Cerdas, Sholeh, dan Berakhlak Mulia.',
            'portal_hero_description' => 'Bergabunglah bersama Sekolah Anak Saleh. Kami menyajikan kurikulum yang mengintegrasikan nilai-nilai Islam dengan pendidikan modern untuk menyiapkan pemimpin masa depan.',
        ];

        foreach ($defaultSettings as $key => $value) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
