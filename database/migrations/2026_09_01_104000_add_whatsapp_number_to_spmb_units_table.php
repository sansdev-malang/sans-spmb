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
        Schema::table('spmb_units', function (Blueprint $table) {
            if (!Schema::hasColumn('spmb_units', 'whatsapp_number')) {
                $table->string('whatsapp_number')->nullable()->after('code');
            }
            if (!Schema::hasColumn('spmb_units', 'admin_contact_name')) {
                $table->string('admin_contact_name')->nullable()->after('whatsapp_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_units', function (Blueprint $table) {
            if (Schema::hasColumn('spmb_units', 'whatsapp_number')) {
                $table->dropColumn('whatsapp_number');
            }
            if (Schema::hasColumn('spmb_units', 'admin_contact_name')) {
                $table->dropColumn('admin_contact_name');
            }
        });
    }
};
