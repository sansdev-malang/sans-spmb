<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SpmbFormField;
use App\Models\SpmbFormStep;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SpmbFormField::truncate();
        SpmbFormStep::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Form Steps (6 Steps, direct to Informasi Calon Murid)
        $formSteps = [
            ['id' => 1, 'title' => 'Informasi Calon Murid', 'order' => 1, 'is_active' => 1],
            ['id' => 2, 'title' => 'Tempat Tinggal', 'order' => 2, 'is_active' => 1],
            ['id' => 3, 'title' => 'Data Orang Tua', 'order' => 3, 'is_active' => 1],
            ['id' => 4, 'title' => 'Data Wali (Opsional)', 'order' => 4, 'is_active' => 1],
            ['id' => 5, 'title' => 'Data Lampiran', 'order' => 5, 'is_active' => 1],
            ['id' => 6, 'title' => 'Informasi & Referral', 'order' => 6, 'is_active' => 1],
        ];

        foreach ($formSteps as $step) {
            SpmbFormStep::updateOrCreate(['id' => $step['id']], $step);
        }

        // 2. Form Fields
        $formFields = [
            // STEP 1: Informasi Calon Murid
            ['form_step_id' => 1, 'label' => 'Nama Lengkap (Sesuai Akte)', 'field_name' => 'candidate_name', 'type' => 'text', 'options' => null, 'is_required' => 1, 'order' => 1],
            ['form_step_id' => 1, 'label' => 'Nama Panggilan', 'field_name' => 'nickname', 'type' => 'text', 'options' => null, 'is_required' => 0, 'order' => 2],
            ['form_step_id' => 1, 'label' => 'NIK (Nomor Induk Kependudukan)', 'field_name' => 'nik', 'type' => 'number', 'options' => null, 'is_required' => 0, 'order' => 3],
            ['form_step_id' => 1, 'label' => 'Nomor Kartu Keluarga (KK)', 'field_name' => 'family_card_no', 'type' => 'number', 'options' => null, 'is_required' => 1, 'order' => 4],
            ['form_step_id' => 1, 'label' => 'Jenis Kelamin', 'field_name' => 'gender', 'type' => 'select', 'options' => 'Laki-laki,Perempuan', 'is_required' => 1, 'order' => 5],
            ['form_step_id' => 1, 'label' => 'Agama', 'field_name' => 'religion', 'type' => 'select', 'options' => 'Islam,Kristen,Katolik,Hindu,Budha,Konghucu', 'is_required' => 1, 'order' => 6],
            ['form_step_id' => 1, 'label' => 'Tempat Lahir', 'field_name' => 'birth_place', 'type' => 'text', 'options' => null, 'is_required' => 1, 'order' => 7],
            ['form_step_id' => 1, 'label' => 'Tanggal Lahir', 'field_name' => 'birth_date', 'type' => 'date', 'options' => null, 'is_required' => 1, 'order' => 8],
            ['form_step_id' => 1, 'label' => 'Asal Sekolah', 'field_name' => 'previous_school', 'type' => 'text', 'options' => null, 'is_required' => 0, 'order' => 9],

            // STEP 2: Tempat Tinggal
            ['form_step_id' => 2, 'label' => 'Provinsi', 'field_name' => 'province', 'type' => 'select', 'options' => null, 'is_required' => 1, 'order' => 1],
            ['form_step_id' => 2, 'label' => 'Kabupaten / Kota', 'field_name' => 'city', 'type' => 'select', 'options' => null, 'is_required' => 1, 'order' => 2],
            ['form_step_id' => 2, 'label' => 'Kecamatan', 'field_name' => 'kecamatan', 'type' => 'select', 'options' => null, 'is_required' => 1, 'order' => 3],
            ['form_step_id' => 2, 'label' => 'Kelurahan / Desa', 'field_name' => 'kelurahan', 'type' => 'select', 'options' => null, 'is_required' => 1, 'order' => 4],
            ['form_step_id' => 2, 'label' => 'Alamat Jalan / Perumahan', 'field_name' => 'address', 'type' => 'text', 'options' => null, 'is_required' => 1, 'order' => 5],
            ['form_step_id' => 2, 'label' => 'Nomor Rumah', 'field_name' => 'house_number', 'type' => 'text', 'options' => null, 'is_required' => 0, 'order' => 6],
            ['form_step_id' => 2, 'label' => 'RT', 'field_name' => 'rt', 'type' => 'text', 'options' => null, 'is_required' => 0, 'order' => 7],
            ['form_step_id' => 2, 'label' => 'RW', 'field_name' => 'rw', 'type' => 'text', 'options' => null, 'is_required' => 0, 'order' => 8],

            // STEP 3: Data Orang Tua
            ['form_step_id' => 3, 'label' => 'Nama Ayah Kandung', 'field_name' => 'father_name', 'type' => 'text', 'options' => null, 'is_required' => 1, 'order' => 1],
            ['form_step_id' => 3, 'label' => 'NIK Ayah Kandung', 'field_name' => 'father_nik', 'type' => 'number', 'options' => null, 'is_required' => 1, 'order' => 2],
            ['form_step_id' => 3, 'label' => 'Pekerjaan Ayah', 'field_name' => 'father_job', 'type' => 'select', 'options' => 'PNS / ASN,TNI / POLRI,Karyawan BUMN / BUMD,Karyawan Swasta,Wiraswasta / Pengusaha,Dokter / Tenaga Medis,Dosen / Guru,Advokat / Notaris / Konsultan,Pedagang / Petani / Nelayan,Pensiunan,Tidak Bekerja,Lainnya', 'is_required' => 0, 'order' => 3],
            ['form_step_id' => 3, 'label' => 'Handphone / WhatsApp Ayah', 'field_name' => 'father_phone', 'type' => 'number', 'options' => null, 'is_required' => 1, 'order' => 4],
            ['form_step_id' => 3, 'label' => 'Alamat Ayah', 'field_name' => 'father_address', 'type' => 'text', 'options' => null, 'is_required' => 0, 'order' => 5],
            ['form_step_id' => 3, 'label' => 'Nama Ibu Kandung', 'field_name' => 'mother_name', 'type' => 'text', 'options' => null, 'is_required' => 1, 'order' => 6],
            ['form_step_id' => 3, 'label' => 'NIK Ibu Kandung', 'field_name' => 'mother_nik', 'type' => 'number', 'options' => null, 'is_required' => 1, 'order' => 7],
            ['form_step_id' => 3, 'label' => 'Pekerjaan Ibu', 'field_name' => 'mother_job', 'type' => 'select', 'options' => 'Ibu Rumah Tangga,PNS / ASN,TNI / POLRI,Karyawan BUMN / BUMD,Karyawan Swasta,Wiraswasta / Pengusaha,Dokter / Tenaga Medis,Dosen / Guru,Advokat / Notaris / Konsultan,Pedagang / Petani / Nelayan,Pensiunan,Tidak Bekerja,Lainnya', 'is_required' => 0, 'order' => 8],
            ['form_step_id' => 3, 'label' => 'Handphone / WhatsApp Ibu', 'field_name' => 'mother_phone', 'type' => 'number', 'options' => null, 'is_required' => 1, 'order' => 9],
            ['form_step_id' => 3, 'label' => 'Alamat Ibu', 'field_name' => 'mother_address', 'type' => 'text', 'options' => null, 'is_required' => 0, 'order' => 10],

            // STEP 4: Data Wali (Opsional)
            ['form_step_id' => 4, 'label' => 'Nama Wali', 'field_name' => 'guardian_name', 'type' => 'text', 'options' => null, 'is_required' => 0, 'order' => 1],
            ['form_step_id' => 4, 'label' => 'NIK Wali', 'field_name' => 'guardian_nik', 'type' => 'number', 'options' => null, 'is_required' => 0, 'order' => 2],
            ['form_step_id' => 4, 'label' => 'Pekerjaan Wali', 'field_name' => 'guardian_job', 'type' => 'select', 'options' => 'PNS / ASN,TNI / POLRI,Karyawan BUMN / BUMD,Karyawan Swasta,Wiraswasta / Pengusaha,Dokter / Tenaga Medis,Dosen / Guru,Advokat / Notaris / Konsultan,Pedagang / Petani / Nelayan,Pensiunan,Tidak Bekerja,Lainnya', 'is_required' => 0, 'order' => 3],
            ['form_step_id' => 4, 'label' => 'Handphone / WhatsApp Wali', 'field_name' => 'guardian_phone', 'type' => 'number', 'options' => null, 'is_required' => 0, 'order' => 4],
            ['form_step_id' => 4, 'label' => 'Alamat Wali', 'field_name' => 'guardian_address', 'type' => 'text', 'options' => null, 'is_required' => 0, 'order' => 5],

            // STEP 5: Data Lampiran
            ['form_step_id' => 5, 'label' => 'Pas Foto Calon Murid (Foto Formal)', 'field_name' => 'student_photo_path', 'type' => 'file', 'options' => null, 'is_required' => 1, 'order' => 1],
            ['form_step_id' => 5, 'label' => 'Akta Kelahiran', 'field_name' => 'birth_certificate_path', 'type' => 'file', 'options' => null, 'is_required' => 1, 'order' => 2],
            ['form_step_id' => 5, 'label' => 'Kartu Keluarga (KK)', 'field_name' => 'family_card_path', 'type' => 'file', 'options' => null, 'is_required' => 1, 'order' => 3],
            ['form_step_id' => 5, 'label' => 'Ijazah / Surat Keterangan Aktif Sekolah', 'field_name' => 'diploma_certificate_path', 'type' => 'file', 'options' => null, 'is_required' => 0, 'order' => 4],
            ['form_step_id' => 5, 'label' => 'NISN / KIA / Kartu Pelajar (Opsional)', 'field_name' => 'student_card_path', 'type' => 'file', 'options' => null, 'is_required' => 0, 'order' => 5],
            ['form_step_id' => 5, 'label' => 'Asesmen Kebutuhan Khusus (Jika Ada)', 'field_name' => 'special_needs_assessment_path', 'type' => 'file', 'options' => null, 'is_required' => 0, 'order' => 6],

            // STEP 6: Informasi & Referral
            ['form_step_id' => 6, 'label' => 'Saluran Informasi Pendaftaran', 'field_name' => 'info_source', 'type' => 'select', 'options' => 'Media Sosial,Brosur / Spanduk,Website Resmi,Rekomendasi Wali Murid (Referral),Alumni / Keluarga Besar,Lainnya', 'is_required' => 0, 'order' => 1],
        ];

        foreach ($formFields as $field) {
            SpmbFormField::create($field);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No action needed
    }
};
