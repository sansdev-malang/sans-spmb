<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spmb_agreement_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spmb_unit_id')->unique()->constrained('spmb_units')->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->string('rules_consent_label');
            $table->string('fees_consent_label');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spmb_agreement_templates');
    }
};
