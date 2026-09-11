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
        Schema::create('spmb_testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spmb_unit_id')->nullable()->constrained('spmb_units')->nullOnDelete();
            $table->string('name');
            $table->string('role_title')->nullable();
            $table->text('content');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('avatar_url')->nullable();
            $table->integer('order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmb_testimonials');
    }
};
