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
        Schema::table('spmb_units', function (Blueprint $table) {
            $table->text('re_registration_instructions_unpaid')->nullable();
            $table->text('re_registration_instructions_completed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_units', function (Blueprint $table) {
            $table->dropColumn(['re_registration_instructions_unpaid', 're_registration_instructions_completed']);
        });
    }
};
