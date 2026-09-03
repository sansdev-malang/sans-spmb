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
        // 1. Add Ta'aruf Template/Settings to spmb_units
        Schema::table('spmb_units', function (Blueprint $table) {
            $table->string('taaruf_title')->nullable()->after('whatsapp_number');
            $table->string('taaruf_default_location')->nullable()->after('taaruf_title');
            $table->text('taaruf_instructions')->nullable()->after('taaruf_default_location');
            $table->text('taaruf_required_items')->nullable()->after('taaruf_instructions');
        });

        // 2. Add Observation Schedule Fields to registrations
        Schema::table('registrations', function (Blueprint $table) {
            $table->date('observation_date')->nullable()->after('registration_status');
            $table->string('observation_time')->nullable()->after('observation_date');
            $table->string('observation_location')->nullable()->after('observation_time');
            $table->string('observation_interviewer')->nullable()->after('observation_location');
            $table->text('observation_notes')->nullable()->after('observation_interviewer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_units', function (Blueprint $table) {
            $table->dropColumn([
                'taaruf_title',
                'taaruf_default_location',
                'taaruf_instructions',
                'taaruf_required_items'
            ]);
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn([
                'observation_date',
                'observation_time',
                'observation_location',
                'observation_interviewer',
                'observation_notes'
            ]);
        });
    }
};
