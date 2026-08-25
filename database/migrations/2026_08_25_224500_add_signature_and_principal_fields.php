<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spmb_agreement_templates', function (Blueprint $table) {
            $table->string('place')->default('Malang')->after('fees_consent_label');
            $table->string('principal_name')->default('Dra. Hj. Mike Supraptiwi, S.Psi, M.Pd')->after('place');
            $table->string('principal_title')->default('Kepala Sekolah')->after('principal_name');
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->string('signature_name')->nullable()->after('invalid_fields');
            $table->timestamp('signed_at')->nullable()->after('signature_name');
        });
    }

    public function down(): void
    {
        Schema::table('spmb_agreement_templates', function (Blueprint $table) {
            $table->dropColumn(['place', 'principal_name', 'principal_title']);
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['signature_name', 'signed_at']);
        });
    }
};
