<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('spmb_periods', function (Blueprint $table) {
            if (!Schema::hasColumn('spmb_periods', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('is_active');
            }
        });

        Schema::table('spmb_period_unit', function (Blueprint $table) {
            if (!Schema::hasColumn('spmb_period_unit', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('is_active');
            }
        });

        // Set default period to 2027-2028 (or highest active period)
        $defaultPeriodId = DB::table('spmb_periods')
            ->where('is_active', true)
            ->where('year', 'like', '%2027%')
            ->value('id')
            ?? DB::table('spmb_periods')->where('is_active', true)->orderBy('id', 'desc')->value('id')
            ?? DB::table('spmb_periods')->orderBy('id', 'desc')->value('id');

        if ($defaultPeriodId) {
            DB::table('spmb_periods')->where('id', $defaultPeriodId)->update(['is_default' => true]);
            DB::table('spmb_period_unit')->where('spmb_period_id', $defaultPeriodId)->update(['is_default' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_periods', function (Blueprint $table) {
            if (Schema::hasColumn('spmb_periods', 'is_default')) {
                $table->dropColumn('is_default');
            }
        });

        Schema::table('spmb_period_unit', function (Blueprint $table) {
            if (Schema::hasColumn('spmb_period_unit', 'is_default')) {
                $table->dropColumn('is_default');
            }
        });
    }
};
