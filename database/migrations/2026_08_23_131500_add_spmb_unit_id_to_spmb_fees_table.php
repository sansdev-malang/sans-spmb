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
        Schema::table('spmb_fees', function (Blueprint $table) {
            $table->foreignId('spmb_unit_id')
                ->nullable()
                ->after('spmb_fee_category_id')
                ->constrained('spmb_units')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_fees', function (Blueprint $table) {
            $table->dropForeign(['spmb_unit_id']);
            $table->dropColumn('spmb_unit_id');
        });
    }
};
