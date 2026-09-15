<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Pivot Table: Waves per Unit
        Schema::create('spmb_wave_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spmb_wave_id')->constrained('spmb_waves')->cascadeOnDelete();
            $table->foreignId('spmb_unit_id')->constrained('spmb_units')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['spmb_wave_id', 'spmb_unit_id'], 'wave_unit_unique');
        });

        // 2. Pivot Table: Types (Jalur Pendaftaran) per Unit
        Schema::create('spmb_type_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spmb_type_id')->constrained('spmb_types')->cascadeOnDelete();
            $table->foreignId('spmb_unit_id')->constrained('spmb_units')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['spmb_type_id', 'spmb_unit_id'], 'type_unit_unique');
        });

        // 3. Pivot Table: Class Programs (Kategori Murid) per Unit
        Schema::create('spmb_class_program_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spmb_class_program_id')->constrained('spmb_class_programs')->cascadeOnDelete();
            $table->foreignId('spmb_unit_id')->constrained('spmb_units')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['spmb_class_program_id', 'spmb_unit_id'], 'program_unit_unique');
        });

        // 4. Pivot Table: Academic Periods per Unit
        Schema::create('spmb_period_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spmb_period_id')->constrained('spmb_periods')->cascadeOnDelete();
            $table->foreignId('spmb_unit_id')->constrained('spmb_units')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['spmb_period_id', 'spmb_unit_id'], 'period_unit_unique');
        });

        // 5. Pivot Table: Extra Services per Unit
        Schema::create('spmb_extra_service_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spmb_extra_service_id')->constrained('spmb_extra_services')->cascadeOnDelete();
            $table->foreignId('spmb_unit_id')->constrained('spmb_units')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['spmb_extra_service_id', 'spmb_unit_id'], 'service_unit_unique');
        });

        // Auto-seed initial data from existing records across all active units
        $units = DB::table('spmb_units')->get();
        $waves = DB::table('spmb_waves')->get();
        $types = DB::table('spmb_types')->get();
        $programs = DB::table('spmb_class_programs')->get();
        $periods = DB::table('spmb_periods')->get();
        $services = DB::table('spmb_extra_services')->get();
        $now = now();

        foreach ($units as $unit) {
            // Seed waves
            foreach ($waves as $w) {
                DB::table('spmb_wave_unit')->insertOrIgnore([
                    'spmb_wave_id' => $w->id,
                    'spmb_unit_id' => $unit->id,
                    'is_active' => (bool)$w->is_active,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Seed types
            foreach ($types as $t) {
                DB::table('spmb_type_unit')->insertOrIgnore([
                    'spmb_type_id' => $t->id,
                    'spmb_unit_id' => $unit->id,
                    'is_active' => (bool)$t->is_active,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Seed class programs
            foreach ($programs as $p) {
                DB::table('spmb_class_program_unit')->insertOrIgnore([
                    'spmb_class_program_id' => $p->id,
                    'spmb_unit_id' => $unit->id,
                    'is_active' => (bool)$p->is_active,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Seed periods
            foreach ($periods as $per) {
                DB::table('spmb_period_unit')->insertOrIgnore([
                    'spmb_period_id' => $per->id,
                    'spmb_unit_id' => $unit->id,
                    'is_active' => (bool)$per->is_active,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Seed extra services
            foreach ($services as $s) {
                DB::table('spmb_extra_service_unit')->insertOrIgnore([
                    'spmb_extra_service_id' => $s->id,
                    'spmb_unit_id' => $unit->id,
                    'is_active' => (bool)$s->is_active,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmb_extra_service_unit');
        Schema::dropIfExists('spmb_period_unit');
        Schema::dropIfExists('spmb_class_program_unit');
        Schema::dropIfExists('spmb_type_unit');
        Schema::dropIfExists('spmb_wave_unit');
    }
};
