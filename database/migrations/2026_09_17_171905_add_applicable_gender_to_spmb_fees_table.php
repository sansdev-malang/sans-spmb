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
            $table->string('applicable_gender', 20)->nullable()->after('applicable_types')->comment('all, male, female');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_fees', function (Blueprint $table) {
            $table->dropColumn('applicable_gender');
        });
    }
};
