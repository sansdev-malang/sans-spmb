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
            $table->boolean('is_testing')->default(false)->after('applicable_periods');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_fees', function (Blueprint $table) {
            $table->dropColumn('is_testing');
        });
    }
};
