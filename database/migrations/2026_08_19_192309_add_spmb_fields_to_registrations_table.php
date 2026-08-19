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
            $table->foreignId('spmb_period_id')->nullable()->constrained('spmb_periods')->nullOnDelete();
            $table->foreignId('spmb_wave_id')->nullable()->constrained('spmb_waves')->nullOnDelete();
            $table->foreignId('spmb_type_id')->nullable()->constrained('spmb_types')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropForeign(['spmb_period_id']);
            $table->dropForeign(['spmb_wave_id']);
            $table->dropForeign(['spmb_type_id']);
            $table->dropColumn(['spmb_period_id', 'spmb_wave_id', 'spmb_type_id']);
        });
    }
};
