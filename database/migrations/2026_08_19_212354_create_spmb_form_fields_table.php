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
        Schema::create('spmb_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_step_id')->constrained('spmb_form_steps')->onDelete('cascade');
            $table->string('label');
            $table->string('field_name');
            $table->string('type'); // text, number, email, date, select, textarea, file
            $table->text('options')->nullable(); // comma-separated options list
            $table->boolean('is_required')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmb_form_fields');
    }
};
