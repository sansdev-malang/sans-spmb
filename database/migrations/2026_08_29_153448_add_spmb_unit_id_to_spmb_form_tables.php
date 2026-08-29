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
        Schema::table('spmb_form_steps', function (Blueprint $table) {
            $table->foreignId('spmb_unit_id')
                ->nullable()
                ->after('id')
                ->constrained('spmb_units')
                ->nullOnDelete();
        });

        Schema::table('spmb_form_fields', function (Blueprint $table) {
            $table->foreignId('spmb_unit_id')
                ->nullable()
                ->after('form_step_id')
                ->constrained('spmb_units')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_form_fields', function (Blueprint $table) {
            $table->dropForeign(['spmb_unit_id']);
            $table->dropColumn('spmb_unit_id');
        });

        Schema::table('spmb_form_steps', function (Blueprint $table) {
            $table->dropForeign(['spmb_unit_id']);
            $table->dropColumn('spmb_unit_id');
        });
    }
};
