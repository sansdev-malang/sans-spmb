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
        Schema::table('spmb_waves', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
        });

        Schema::table('spmb_types', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
        });

        Schema::table('spmb_class_programs', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_class_programs', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('spmb_types', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('spmb_waves', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
