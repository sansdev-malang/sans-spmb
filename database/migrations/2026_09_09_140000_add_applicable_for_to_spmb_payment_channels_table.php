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
            $table->string('applicable_for')->default('all')->after('fee_value');
        });

        // Set existing QRIS channels to registration_fee by default
        DB::table('spmb_payment_channels')
            ->where('type', 'qris')
            ->orWhere('code', 'like', '%QRIS%')
            ->update(['applicable_for' => 'registration_fee']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_payment_channels', function (Blueprint $table) {
            $table->dropColumn('applicable_for');
        });
    }
};
