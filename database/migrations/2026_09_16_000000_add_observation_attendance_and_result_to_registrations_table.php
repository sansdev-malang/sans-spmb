<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Attendance Confirmation Fields
            $table->string('observation_attendance_status')->nullable()->after('observation_notes'); // 'confirmed_present', 'reschedule_requested', null
            $table->text('observation_attendance_notes')->nullable()->after('observation_attendance_status');
            $table->dateTime('observation_attendance_confirmed_at')->nullable()->after('observation_attendance_notes');

            // Observation Result File & Notes Fields
            $table->string('observation_result_path')->nullable()->after('observation_attendance_confirmed_at');
            $table->text('observation_result_notes')->nullable()->after('observation_result_path');
            $table->dateTime('observation_result_uploaded_at')->nullable()->after('observation_result_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn([
                'observation_attendance_status',
                'observation_attendance_notes',
                'observation_attendance_confirmed_at',
                'observation_result_path',
                'observation_result_notes',
                'observation_result_uploaded_at',
            ]);
        });
    }
};
