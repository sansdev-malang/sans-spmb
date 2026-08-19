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
        Schema::table('spmb_periods', function (Blueprint $table) {
            $table->boolean('is_active')->default(false);
        });
        Schema::table('spmb_waves', function (Blueprint $table) {
            $table->boolean('is_active')->default(false);
        });
        Schema::table('spmb_types', function (Blueprint $table) {
            $table->boolean('is_active')->default(false);
        });
        Schema::table('spmb_fees', function (Blueprint $table) {
            $table->boolean('is_active')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_periods', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
        Schema::table('spmb_waves', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
        Schema::table('spmb_types', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
        Schema::table('spmb_fees', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
