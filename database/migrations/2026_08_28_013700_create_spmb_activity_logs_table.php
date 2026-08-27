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
        Schema::create('spmb_activity_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $blueprint->string('user_name')->default('System');
            $blueprint->string('action');
            $blueprint->text('description');
            $blueprint->string('ip_address')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmb_activity_logs');
    }
};
