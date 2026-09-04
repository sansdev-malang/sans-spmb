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
        Schema::table('spmb_payment_channels', function (Blueprint $table) {
            $table->string('fee_type', 20)->default('flat')->after('type'); // 'flat' or 'percent'
            $table->decimal('fee_value', 12, 2)->default(0.00)->after('fee_type'); // e.g. 4500.00 or 0.70
        });

        // Initialize realistic defaults for existing channels
        DB::table('spmb_payment_channels')->where('type', 'va')->update([
            'fee_type' => 'flat',
            'fee_value' => 4500.00,
        ]);

        DB::table('spmb_payment_channels')->where('type', 'retail')->update([
            'fee_type' => 'flat',
            'fee_value' => 4500.00,
        ]);

        DB::table('spmb_payment_channels')->where('type', 'qris')->update([
            'fee_type' => 'percent',
            'fee_value' => 0.70,
        ]);

        DB::table('spmb_payment_channels')->where('type', 'ewallet')->update([
            'fee_type' => 'percent',
            'fee_value' => 2.00,
        ]);

        // Specific overrides for BNI gateway if any
        $bniGw = DB::table('payment_gateways')->where('code', 'bni')->first();
        if ($bniGw) {
            DB::table('spmb_payment_channels')
                ->where('payment_gateway_id', $bniGw->id)
                ->where('type', 'va')
                ->update([
                    'fee_type' => 'flat',
                    'fee_value' => 1500.00,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_payment_channels', function (Blueprint $table) {
            $table->dropColumn(['fee_type', 'fee_value']);
        });
    }
};
