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
        Schema::table('spmb_grades', function (Blueprint $table) {
            $table->unsignedSmallInteger('min_age_years')->nullable()->after('is_active');
            $table->unsignedSmallInteger('min_age_months')->default(0)->after('min_age_years');
            $table->unsignedSmallInteger('max_age_years')->nullable()->after('min_age_months');
            $table->unsignedSmallInteger('max_age_months')->default(0)->after('max_age_years');
            $table->string('age_notes')->nullable()->after('max_age_months');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_grades', function (Blueprint $table) {
            $table->dropColumn([
                'min_age_years',
                'min_age_months',
                'max_age_years',
                'max_age_months',
                'age_notes',
            ]);
        });
    }
};
