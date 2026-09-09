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
        // 1. Add default room & address to spmb_units
        Schema::table('spmb_units', function (Blueprint $table) {
            if (!Schema::hasColumn('spmb_units', 'taaruf_default_room')) {
                $table->string('taaruf_default_room')->nullable()->after('taaruf_default_location');
            }
            if (!Schema::hasColumn('spmb_units', 'taaruf_default_address')) {
                $table->text('taaruf_default_address')->nullable()->after('taaruf_default_room');
            }
        });

        // 2. Add observation room & address to registrations
        Schema::table('registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('registrations', 'observation_room')) {
                $table->string('observation_room')->nullable()->after('observation_location');
            }
            if (!Schema::hasColumn('registrations', 'observation_address')) {
                $table->text('observation_address')->nullable()->after('observation_room');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_units', function (Blueprint $table) {
            $table->dropColumn([
                'taaruf_default_room',
                'taaruf_default_address',
            ]);
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn([
                'observation_room',
                'observation_address',
            ]);
        });
    }
};
