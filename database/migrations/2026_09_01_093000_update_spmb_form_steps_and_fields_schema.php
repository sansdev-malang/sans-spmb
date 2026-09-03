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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $columns = [
                'family_card_no', 'address', 'house_number', 'rt', 'rw', 'kelurahan', 'kecamatan', 'city', 'province',
                'father_nik', 'father_address', 'father_phone', 'mother_nik', 'mother_address', 'mother_phone',
                'guardian_name', 'guardian_nik', 'guardian_address', 'guardian_phone',
                'student_photo_path', 'diploma_certificate_path', 'student_card_path', 'special_needs_assessment_path', 'payment_receipt_path'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('registrations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
