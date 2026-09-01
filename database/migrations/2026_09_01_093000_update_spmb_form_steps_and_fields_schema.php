<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\SpmbFormStep;
use App\Models\SpmbFormField;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Informasi Calon Siswa
            if (!Schema::hasColumn('registrations', 'family_card_no')) {
                $table->string('family_card_no')->nullable()->after('nik');
            }

            // Tempat Tinggal
            if (!Schema::hasColumn('registrations', 'address')) {
                $table->text('address')->nullable()->after('admission_level');
            }
            if (!Schema::hasColumn('registrations', 'house_number')) {
                $table->string('house_number')->nullable()->after('address');
            }
            if (!Schema::hasColumn('registrations', 'rt')) {
                $table->string('rt')->nullable()->after('house_number');
            }
            if (!Schema::hasColumn('registrations', 'rw')) {
                $table->string('rw')->nullable()->after('rt');
            }
            if (!Schema::hasColumn('registrations', 'kelurahan')) {
                $table->string('kelurahan')->nullable()->after('rw');
            }
            if (!Schema::hasColumn('registrations', 'kecamatan')) {
                $table->string('kecamatan')->nullable()->after('kelurahan');
            }
            if (!Schema::hasColumn('registrations', 'city')) {
                $table->string('city')->nullable()->after('kecamatan');
            }
            if (!Schema::hasColumn('registrations', 'province')) {
                $table->string('province')->nullable()->after('city');
            }

            // Data Orang Tua
            if (!Schema::hasColumn('registrations', 'father_nik')) {
                $table->string('father_nik')->nullable()->after('father_name');
            }
            if (!Schema::hasColumn('registrations', 'father_address')) {
                $table->text('father_address')->nullable()->after('father_nik');
            }
            if (!Schema::hasColumn('registrations', 'father_phone')) {
                $table->string('father_phone')->nullable()->after('father_address');
            }
            if (!Schema::hasColumn('registrations', 'mother_nik')) {
                $table->string('mother_nik')->nullable()->after('mother_name');
            }
            if (!Schema::hasColumn('registrations', 'mother_address')) {
                $table->text('mother_address')->nullable()->after('mother_nik');
            }
            if (!Schema::hasColumn('registrations', 'mother_phone')) {
                $table->string('mother_phone')->nullable()->after('mother_address');
            }

            // Data Wali
            if (!Schema::hasColumn('registrations', 'guardian_name')) {
                $table->string('guardian_name')->nullable()->after('parent_phone');
            }
            if (!Schema::hasColumn('registrations', 'guardian_nik')) {
                $table->string('guardian_nik')->nullable()->after('guardian_name');
            }
            if (!Schema::hasColumn('registrations', 'guardian_address')) {
                $table->text('guardian_address')->nullable()->after('guardian_nik');
            }
            if (!Schema::hasColumn('registrations', 'guardian_phone')) {
                $table->string('guardian_phone')->nullable()->after('guardian_address');
            }

            // Data Lampiran
            if (!Schema::hasColumn('registrations', 'student_photo_path')) {
                $table->string('student_photo_path')->nullable()->after('family_card_path');
            }
            if (!Schema::hasColumn('registrations', 'diploma_certificate_path')) {
                $table->string('diploma_certificate_path')->nullable()->after('student_photo_path');
            }
            if (!Schema::hasColumn('registrations', 'student_card_path')) {
                $table->string('student_card_path')->nullable()->after('diploma_certificate_path');
            }
            if (!Schema::hasColumn('registrations', 'special_needs_assessment_path')) {
                $table->string('special_needs_assessment_path')->nullable()->after('student_card_path');
            }
            if (!Schema::hasColumn('registrations', 'payment_receipt_path')) {
                $table->string('payment_receipt_path')->nullable()->after('special_needs_assessment_path');
            }
        });

        // Seed / Update SpmbFormStep & SpmbFormField
        $steps = [
            ['id' => 1, 'title' => 'Pilihan Program & Layanan', 'order' => 1, 'is_active' => 1],
            ['id' => 2, 'title' => 'Informasi Calon Siswa', 'order' => 2, 'is_active' => 1],
            ['id' => 3, 'title' => 'Tempat Tinggal', 'order' => 3, 'is_active' => 1],
            ['id' => 4, 'title' => 'Data Orang Tua', 'order' => 4, 'is_active' => 1],
            ['id' => 5, 'title' => 'Data Wali (Opsional)', 'order' => 5, 'is_active' => 1],
            ['id' => 6, 'title' => 'Data Lampiran', 'order' => 6, 'is_active' => 1],
        ];

        foreach ($steps as $s) {
            SpmbFormStep::updateOrCreate(['id' => $s['id']], $s);
        }

        // Delete previous fields to prevent duplicate order mapping
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SpmbFormField::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $fields = [
            // STEP 1: Pilihan Program & Layanan
            [
                'form_step_id' => 1,
                'label' => 'Tahun Ajaran',
                'field_name' => 'spmb_period_id',
                'type' => 'select',
                'options' => null,
                'is_required' => 1,
                'order' => 1,
            ],
            [
                'form_step_id' => 1,
                'label' => 'Gelombang Pendaftaran',
                'field_name' => 'spmb_wave_id',
                'type' => 'select',
                'options' => null,
                'is_required' => 1,
                'order' => 2,
            ],
            [
                'form_step_id' => 1,
                'label' => 'Jalur Pendaftaran',
                'field_name' => 'spmb_type_id',
                'type' => 'select',
                'options' => null,
                'is_required' => 1,
                'order' => 3,
            ],
            [
                'form_step_id' => 1,
                'label' => 'Program Kelas',
                'field_name' => 'spmb_class_program_id',
                'type' => 'select',
                'options' => null,
                'is_required' => 1,
                'order' => 4,
            ],
            [
                'form_step_id' => 1,
                'label' => 'Layanan Non-Formal',
                'field_name' => 'extra_services',
                'type' => 'select',
                'options' => null,
                'is_required' => 0,
                'order' => 5,
            ],

            // STEP 2: Informasi Calon Siswa
            [
                'form_step_id' => 2,
                'label' => 'Nama Lengkap (Sesuai Akte)',
                'field_name' => 'candidate_name',
                'type' => 'text',
                'options' => null,
                'is_required' => 1,
                'order' => 1,
            ],
            [
                'form_step_id' => 2,
                'label' => 'Nama Panggilan',
                'field_name' => 'nickname',
                'type' => 'text',
                'options' => null,
                'is_required' => 0,
                'order' => 2,
            ],
            [
                'form_step_id' => 2,
                'label' => 'NIK (Nomor Induk Kependudukan)',
                'field_name' => 'nik',
                'type' => 'number',
                'options' => null,
                'is_required' => 0,
                'order' => 3,
            ],
            [
                'form_step_id' => 2,
                'label' => 'Nomor Kartu Keluarga (KK)',
                'field_name' => 'family_card_no',
                'type' => 'number',
                'options' => null,
                'is_required' => 1,
                'order' => 4,
            ],
            [
                'form_step_id' => 2,
                'label' => 'Jenis Kelamin',
                'field_name' => 'gender',
                'type' => 'select',
                'options' => 'Laki-laki,Perempuan',
                'is_required' => 1,
                'order' => 5,
            ],
            [
                'form_step_id' => 2,
                'label' => 'Agama',
                'field_name' => 'religion',
                'type' => 'select',
                'options' => 'Islam,Kristen,Katolik,Hindu,Budha,Konghucu',
                'is_required' => 1,
                'order' => 6,
            ],
            [
                'form_step_id' => 2,
                'label' => 'Tempat Lahir',
                'field_name' => 'birth_place',
                'type' => 'text',
                'options' => null,
                'is_required' => 1,
                'order' => 7,
            ],
            [
                'form_step_id' => 2,
                'label' => 'Tanggal Lahir',
                'field_name' => 'birth_date',
                'type' => 'date',
                'options' => null,
                'is_required' => 1,
                'order' => 8,
            ],
            [
                'form_step_id' => 2,
                'label' => 'Asal Sekolah',
                'field_name' => 'previous_school',
                'type' => 'text',
                'options' => null,
                'is_required' => 0,
                'order' => 9,
            ],

            // STEP 3: Tempat Tinggal
            [
                'form_step_id' => 3,
                'label' => 'Alamat Tempat Tinggal',
                'field_name' => 'address',
                'type' => 'text',
                'options' => null,
                'is_required' => 1,
                'order' => 1,
            ],
            [
                'form_step_id' => 3,
                'label' => 'Nomor Rumah',
                'field_name' => 'house_number',
                'type' => 'text',
                'options' => null,
                'is_required' => 0,
                'order' => 2,
            ],
            [
                'form_step_id' => 3,
                'label' => 'RT',
                'field_name' => 'rt',
                'type' => 'text',
                'options' => null,
                'is_required' => 0,
                'order' => 3,
            ],
            [
                'form_step_id' => 3,
                'label' => 'RW',
                'field_name' => 'rw',
                'type' => 'text',
                'options' => null,
                'is_required' => 0,
                'order' => 4,
            ],
            [
                'form_step_id' => 3,
                'label' => 'Kelurahan / Desa',
                'field_name' => 'kelurahan',
                'type' => 'text',
                'options' => null,
                'is_required' => 1,
                'order' => 5,
            ],
            [
                'form_step_id' => 3,
                'label' => 'Kecamatan',
                'field_name' => 'kecamatan',
                'type' => 'text',
                'options' => null,
                'is_required' => 1,
                'order' => 6,
            ],
            [
                'form_step_id' => 3,
                'label' => 'Kabupaten / Kota',
                'field_name' => 'city',
                'type' => 'text',
                'options' => null,
                'is_required' => 1,
                'order' => 7,
            ],
            [
                'form_step_id' => 3,
                'label' => 'Provinsi',
                'field_name' => 'province',
                'type' => 'text',
                'options' => null,
                'is_required' => 1,
                'order' => 8,
            ],

            // STEP 4: Data Orang Tua
            [
                'form_step_id' => 4,
                'label' => 'Nama Ayah Kandung',
                'field_name' => 'father_name',
                'type' => 'text',
                'options' => null,
                'is_required' => 1,
                'order' => 1,
            ],
            [
                'form_step_id' => 4,
                'label' => 'NIK Ayah Kandung',
                'field_name' => 'father_nik',
                'type' => 'number',
                'options' => null,
                'is_required' => 1,
                'order' => 2,
            ],
            [
                'form_step_id' => 4,
                'label' => 'Alamat Ayah',
                'field_name' => 'father_address',
                'type' => 'text',
                'options' => null,
                'is_required' => 0,
                'order' => 3,
            ],
            [
                'form_step_id' => 4,
                'label' => 'Handphone / WhatsApp Ayah',
                'field_name' => 'father_phone',
                'type' => 'number',
                'options' => null,
                'is_required' => 1,
                'order' => 4,
            ],
            [
                'form_step_id' => 4,
                'label' => 'Nama Ibu Kandung',
                'field_name' => 'mother_name',
                'type' => 'text',
                'options' => null,
                'is_required' => 1,
                'order' => 5,
            ],
            [
                'form_step_id' => 4,
                'label' => 'NIK Ibu Kandung',
                'field_name' => 'mother_nik',
                'type' => 'number',
                'options' => null,
                'is_required' => 1,
                'order' => 6,
            ],
            [
                'form_step_id' => 4,
                'label' => 'Alamat Ibu',
                'field_name' => 'mother_address',
                'type' => 'text',
                'options' => null,
                'is_required' => 0,
                'order' => 7,
            ],
            [
                'form_step_id' => 4,
                'label' => 'Handphone / WhatsApp Ibu',
                'field_name' => 'mother_phone',
                'type' => 'number',
                'options' => null,
                'is_required' => 1,
                'order' => 8,
            ],

            // STEP 5: Data Wali (Opsional)
            [
                'form_step_id' => 5,
                'label' => 'Nama Wali',
                'field_name' => 'guardian_name',
                'type' => 'text',
                'options' => null,
                'is_required' => 0,
                'order' => 1,
            ],
            [
                'form_step_id' => 5,
                'label' => 'NIK Wali',
                'field_name' => 'guardian_nik',
                'type' => 'number',
                'options' => null,
                'is_required' => 0,
                'order' => 2,
            ],
            [
                'form_step_id' => 5,
                'label' => 'Alamat Wali',
                'field_name' => 'guardian_address',
                'type' => 'text',
                'options' => null,
                'is_required' => 0,
                'order' => 3,
            ],
            [
                'form_step_id' => 5,
                'label' => 'Handphone / WhatsApp Wali',
                'field_name' => 'guardian_phone',
                'type' => 'number',
                'options' => null,
                'is_required' => 0,
                'order' => 4,
            ],

            // STEP 6: Data Lampiran
            [
                'form_step_id' => 6,
                'label' => 'Pas Foto Murid (Background Formal / Max 2 MB)',
                'field_name' => 'student_photo_path',
                'type' => 'file',
                'options' => null,
                'is_required' => 1,
                'order' => 1,
            ],
            [
                'form_step_id' => 6,
                'label' => 'Kartu Keluarga',
                'field_name' => 'family_card_path',
                'type' => 'file',
                'options' => null,
                'is_required' => 1,
                'order' => 2,
            ],
            [
                'form_step_id' => 6,
                'label' => 'Ijazah Terakhir',
                'field_name' => 'diploma_certificate_path',
                'type' => 'file',
                'options' => null,
                'is_required' => 0,
                'order' => 3,
            ],
            [
                'form_step_id' => 6,
                'label' => 'NISN / Kartu Pelajar (Opsional)',
                'field_name' => 'student_card_path',
                'type' => 'file',
                'options' => null,
                'is_required' => 0,
                'order' => 4,
            ],
            [
                'form_step_id' => 6,
                'label' => 'Akta Kelahiran',
                'field_name' => 'birth_certificate_path',
                'type' => 'file',
                'options' => null,
                'is_required' => 1,
                'order' => 5,
            ],
            [
                'form_step_id' => 6,
                'label' => 'Assesmen Kebutuhan Khusus (Jika Ada)',
                'field_name' => 'special_needs_assessment_path',
                'type' => 'file',
                'options' => null,
                'is_required' => 0,
                'order' => 6,
            ],
            [
                'form_step_id' => 6,
                'label' => 'Bukti Pembayaran Biaya Pendaftaran',
                'field_name' => 'payment_receipt_path',
                'type' => 'file',
                'options' => null,
                'is_required' => 0,
                'order' => 7,
            ],
        ];

        foreach ($fields as $f) {
            SpmbFormField::create($f);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep steps and fields intact on rollback
    }
};
