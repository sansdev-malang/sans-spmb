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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Step 1: Candidate Info
            $table->string('candidate_name')->nullable();
            $table->string('nickname')->nullable();
            $table->string('nik')->nullable();
            $table->string('gender')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('religion')->nullable();
            $table->string('previous_school')->nullable();
            $table->string('admission_level')->nullable(); // e.g. Play Group, TK A, TK B
            
            // Step 2: Parent Info
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('parent_phone')->nullable();
            
            // Step 3: Documents
            $table->string('birth_certificate_path')->nullable();
            $table->string('family_card_path')->nullable();
            
            // Statuses
            $table->string('registration_status')->default('draft'); // draft, submitted, verified, failed
            $table->string('payment_status')->default('unpaid'); // unpaid, pending, paid, expired
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
