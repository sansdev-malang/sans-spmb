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
        Schema::table('spmb_fee_categories', function (Blueprint $table) {
            $table->json('applicable_periods')->nullable()->after('category_type');
        });

        Schema::table('spmb_fees', function (Blueprint $table) {
            $table->json('applicable_periods')->nullable()->after('applicable_gender');
        });

        // Populate existing fees and categories with current period IDs so existing data remains active
        try {
            $allPeriodIds = DB::table('spmb_periods')->pluck('id')->toArray();
            if (!empty($allPeriodIds)) {
                $jsonPeriods = json_encode(array_values(array_map('intval', $allPeriodIds)));
                DB::table('spmb_fee_categories')->whereNull('applicable_periods')->update(['applicable_periods' => $jsonPeriods]);
                DB::table('spmb_fees')->whereNull('applicable_periods')->update(['applicable_periods' => $jsonPeriods]);
            }
        } catch (\Exception $e) {
            // Ignore if tables are empty
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_fee_categories', function (Blueprint $table) {
            $table->dropColumn('applicable_periods');
        });

        Schema::table('spmb_fees', function (Blueprint $table) {
            $table->dropColumn('applicable_periods');
        });
    }
};
