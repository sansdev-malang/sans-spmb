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
        Schema::create('api_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('client_key')->unique();
            $table->string('api_token_hash')->unique();
            $table->string('token_preview')->nullable(); // e.g. spmb_live_...9a2x
            $table->json('allowed_units')->nullable();   // ['sd', 'smp', 'paud'] or ['all']
            $table->json('allowed_statuses')->nullable();// ['verified', 'accepted'] or ['all']
            $table->json('allowed_fields')->nullable();  // ['bio', 'parents', 'school_origin', 'documents', 'payments']
            $table->string('webhook_url')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->json('webhook_events')->nullable();  // ['candidate.verified', 'payment.success']
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_clients');
    }
};
