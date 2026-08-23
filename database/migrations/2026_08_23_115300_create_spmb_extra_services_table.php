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
        Schema::create('spmb_extra_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('registration_extra_service', function (Blueprint $table) {
            $table->foreignId('registration_id')
                  ->constrained('registrations')
                  ->cascadeOnDelete();
            
            $table->foreignId('spmb_extra_service_id')
                  ->constrained('spmb_extra_services')
                  ->cascadeOnDelete();

            $table->primary(['registration_id', 'spmb_extra_service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_extra_service');
        Schema::dropIfExists('spmb_extra_services');
    }
};
