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
            if (!Schema::hasColumn('registrations', 'father_job')) {
                $table->string('father_job')->nullable()->after('father_nik');
            }
            if (!Schema::hasColumn('registrations', 'mother_job')) {
                $table->string('mother_job')->nullable()->after('mother_nik');
            }
            if (!Schema::hasColumn('registrations', 'guardian_job')) {
                $table->string('guardian_job')->nullable()->after('guardian_nik');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            if (Schema::hasColumn('registrations', 'father_job')) {
                $table->dropColumn('father_job');
            }
            if (Schema::hasColumn('registrations', 'mother_job')) {
                $table->dropColumn('mother_job');
            }
            if (Schema::hasColumn('registrations', 'guardian_job')) {
                $table->dropColumn('guardian_job');
            }
        });
    }
};
