<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create Candidate test user
        $candidate = User::factory()->create([
            'name' => 'Calon Orang Tua Siswa',
            'email' => 'candidate@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'candidate'
        ]);

        // Seed default SPMB Master settings
        $p1 = \App\Models\SpmbPeriod::create(['year' => '2024-2025', 'is_active' => true]);
        $p2 = \App\Models\SpmbPeriod::create(['year' => '2025-2026', 'is_active' => false]);

        $w1 = \App\Models\SpmbWave::create(['name' => 'Indent', 'is_active' => true]);
        $w2 = \App\Models\SpmbWave::create(['name' => 'Gelombang 1', 'is_active' => true]);
        $w3 = \App\Models\SpmbWave::create(['name' => 'Gelombang 2', 'is_active' => false]);

        $t1 = \App\Models\SpmbType::create(['name' => 'Kelas 1 Baru', 'is_active' => true]);
        $t2 = \App\Models\SpmbType::create(['name' => 'Mutasi Masuk / Pindahan', 'is_active' => false]);

        // Seed default Fees & Categories
        \App\Models\SpmbFeeCategory::create(['name' => 'Biaya Pendaftaran']);
        \App\Models\SpmbFeeCategory::create(['name' => 'Uang Kegiatan']);

        \App\Models\SpmbFee::create(['name' => 'Pendaftaran Play Group', 'amount' => 250000, 'is_active' => false]);
        \App\Models\SpmbFee::create(['name' => 'Pendaftaran TK A', 'amount' => 350000, 'is_active' => true]);

        // Seed default Winpay API settings
        \App\Models\Setting::set('winpay_mode', 'simulator');
        \App\Models\Setting::set('winpay_merchant_id', 'MOCK_MERCHANT_ID');
        \App\Models\Setting::set('winpay_client_key', 'MOCK_CLIENT_KEY');
        \App\Models\Setting::set('winpay_client_secret', 'MOCK_CLIENT_SECRET');

        // Seed default payment channels
        \App\Models\SpmbPaymentChannel::create(['code' => 'MANDIRI', 'name' => 'Mandiri Virtual Account', 'type' => 'Virtual Account', 'is_active' => true]);
        \App\Models\SpmbPaymentChannel::create(['code' => 'BRI', 'name' => 'BRI Virtual Account', 'type' => 'Virtual Account', 'is_active' => true]);
        \App\Models\SpmbPaymentChannel::create(['code' => 'BNI', 'name' => 'BNI Virtual Account', 'type' => 'Virtual Account', 'is_active' => true]);
        \App\Models\SpmbPaymentChannel::create(['code' => 'BCA', 'name' => 'BCA Virtual Account', 'type' => 'Virtual Account', 'is_active' => true]);
        \App\Models\SpmbPaymentChannel::create(['code' => 'QRIS', 'name' => 'QRIS (GPN) - Scan e-Wallet & m-Banking', 'type' => 'QR Code Payment', 'is_active' => true]);
        
        \App\Models\Setting::set('winpay_sandbox_merchant_id', 'SANDBOX_MERCHANT_ID');
        \App\Models\Setting::set('winpay_sandbox_client_key', 'SANDBOX_CLIENT_KEY');
        \App\Models\Setting::set('winpay_sandbox_client_secret', 'SANDBOX_CLIENT_SECRET');

        // Automatically initialize registration draft for candidate pre-populated with test values
        \App\Models\Registration::create([
            'user_id' => $candidate->id,
            'candidate_name' => 'Ahmad Raihan',
            'nickname' => 'Raihan',
            'nik' => '3201020304050607',
            'gender' => 'Laki-laki',
            'religion' => 'Islam',
            'birth_place' => 'Bandung',
            'birth_date' => '2019-08-15',
            'previous_school' => 'TK Al-Ikhlas',
            'admission_level' => 'TK A',
            'father_name' => 'Budi Santoso',
            'mother_name' => 'Siti Aminah',
            'parent_phone' => '081234567890',
            'birth_certificate_path' => 'documents/mock_birth_certificate.pdf',
            'family_card_path' => 'documents/mock_family_card.pdf',
            'registration_status' => 'draft',
            'payment_status' => 'unpaid',
            'spmb_period_id' => $p1->id,
            'spmb_wave_id' => $w2->id,
            'spmb_type_id' => $t1->id,
        ]);

        // Seed default Dynamic Form Steps and Fields
        $step1 = \App\Models\SpmbFormStep::create(['title' => 'Informasi Calon Siswa', 'order' => 1]);
        $step2 = \App\Models\SpmbFormStep::create(['title' => 'Data Orang Tua / Wali', 'order' => 2]);
        $step3 = \App\Models\SpmbFormStep::create(['title' => 'Dokumen Persyaratan', 'order' => 3]);

        // Step 1 Fields
        $step1->fields()->createMany([
            ['label' => 'Nama Lengkap (Sesuai Akte)', 'field_name' => 'candidate_name', 'type' => 'text', 'is_required' => true, 'order' => 1],
            ['label' => 'Nama Panggilan', 'field_name' => 'nickname', 'type' => 'text', 'is_required' => false, 'order' => 2],
            ['label' => 'NIK (Nomor Induk Kependudukan)', 'field_name' => 'nik', 'type' => 'number', 'is_required' => true, 'order' => 3],
            ['label' => 'Jenis Kelamin', 'field_name' => 'gender', 'type' => 'select', 'options' => 'Laki-laki,Perempuan', 'is_required' => true, 'order' => 4],
            ['label' => 'Agama', 'field_name' => 'religion', 'type' => 'text', 'is_required' => true, 'order' => 5],
            ['label' => 'Tempat Lahir', 'field_name' => 'birth_place', 'type' => 'text', 'is_required' => true, 'order' => 6],
            ['label' => 'Tanggal Lahir', 'field_name' => 'birth_date', 'type' => 'date', 'is_required' => true, 'order' => 7],
            ['label' => 'Asal Sekolah (TK/RA/PAUD)', 'field_name' => 'previous_school', 'type' => 'text', 'is_required' => false, 'order' => 8],
            ['label' => 'Tingkat Pendaftaran', 'field_name' => 'admission_level', 'type' => 'select', 'options' => 'Play Group,TK A,TK B', 'is_required' => true, 'order' => 9],
        ]);

        // Step 2 Fields
        $step2->fields()->createMany([
            ['label' => 'Nama Ayah Kandung', 'field_name' => 'father_name', 'type' => 'text', 'is_required' => true, 'order' => 1],
            ['label' => 'Nama Ibu Kandung', 'field_name' => 'mother_name', 'type' => 'text', 'is_required' => true, 'order' => 2],
            ['label' => 'No. HP Wali (WhatsApp)', 'field_name' => 'parent_phone', 'type' => 'number', 'is_required' => true, 'order' => 3],
        ]);

        // Step 3 Fields
        $step3->fields()->createMany([
            ['label' => 'Scan Akta Kelahiran', 'field_name' => 'birth_certificate_path', 'type' => 'file', 'is_required' => true, 'order' => 1],
            ['label' => 'Scan Kartu Keluarga', 'field_name' => 'family_card_path', 'type' => 'file', 'is_required' => true, 'order' => 2],
        ]);

        // Create Admin test user
        User::factory()->create([
            'name' => 'Panitia SPMB',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin'
        ]);
    }
}
