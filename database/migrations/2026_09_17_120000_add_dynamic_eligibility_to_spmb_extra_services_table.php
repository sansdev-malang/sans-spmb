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
        Schema::table('spmb_extra_services', function (Blueprint $table) {
            $table->json('applicable_types')->nullable()->after('is_active');
            $table->json('applicable_class_programs')->nullable()->after('applicable_types');
            $table->json('applicable_waves')->nullable()->after('applicable_class_programs');
            $table->json('applicable_periods')->nullable()->after('applicable_waves');
            $table->json('applicable_grades')->nullable()->after('applicable_periods');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_extra_services', function (Blueprint $table) {
            $table->dropColumn([
                'applicable_types',
                'applicable_class_programs',
                'applicable_waves',
                'applicable_periods',
                'applicable_grades'
            ]);
        });
    }
};
