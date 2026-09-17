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
            if (!Schema::hasColumn('registrations', 'spmb_secondary_grade_id')) {
                $table->foreignId('spmb_secondary_grade_id')
                    ->nullable()
                    ->after('spmb_grade_id')
                    ->constrained('spmb_grades')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            if (Schema::hasColumn('registrations', 'spmb_secondary_grade_id')) {
                $table->dropForeign(['spmb_secondary_grade_id']);
                $table->dropColumn('spmb_secondary_grade_id');
            }
        });
    }
};
