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
            $table->boolean('is_testing')->default(false)->after('is_active');
        });

        Schema::table('spmb_waves', function (Blueprint $table) {
            $table->boolean('is_testing')->default(false)->after('description');
        });

        Schema::table('spmb_types', function (Blueprint $table) {
            $table->boolean('is_testing')->default(false)->after('description');
        });

        Schema::table('spmb_class_programs', function (Blueprint $table) {
            $table->boolean('is_testing')->default(false)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_periods', function (Blueprint $table) {
            $table->dropColumn('is_testing');
        });

        Schema::table('spmb_waves', function (Blueprint $table) {
            $table->dropColumn('is_testing');
        });

        Schema::table('spmb_types', function (Blueprint $table) {
            $table->dropColumn('is_testing');
        });

        Schema::table('spmb_class_programs', function (Blueprint $table) {
            $table->dropColumn('is_testing');
        });
    }
};
