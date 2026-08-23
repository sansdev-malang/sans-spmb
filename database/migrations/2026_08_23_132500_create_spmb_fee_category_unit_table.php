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
        Schema::create('spmb_fee_category_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spmb_fee_category_id')
                ->constrained('spmb_fee_categories')
                ->onDelete('cascade');
            $table->foreignId('spmb_unit_id')
                ->constrained('spmb_units')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmb_fee_category_unit');
    }
};
