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
            $table->foreignId('spmb_unit_id')->nullable()->constrained('spmb_units')->nullOnDelete();
            $table->foreignId('spmb_grade_id')->nullable()->constrained('spmb_grades')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropForeign(['spmb_unit_id']);
            $table->dropForeign(['spmb_grade_id']);
            $table->dropColumn(['spmb_unit_id', 'spmb_grade_id']);
        });
    }
};
