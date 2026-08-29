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
        Schema::create('spmb_form_step_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spmb_form_step_id')
                ->constrained('spmb_form_steps')
                ->cascadeOnDelete();
            $table->foreignId('spmb_unit_id')
                ->constrained('spmb_units')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['spmb_form_step_id', 'spmb_unit_id'], 'step_unit_unique');
        });

        Schema::create('spmb_form_field_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spmb_form_field_id')
                ->constrained('spmb_form_fields')
                ->cascadeOnDelete();
            $table->foreignId('spmb_unit_id')
                ->constrained('spmb_units')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['spmb_form_field_id', 'spmb_unit_id'], 'field_unit_unique');
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmb_form_field_unit');
        Schema::dropIfExists('spmb_form_step_unit');
    }
};
