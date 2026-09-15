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
        Schema::table('spmb_fees', function (Blueprint $table) {
            $table->json('applicable_grades')->nullable()->after('is_active');
            $table->json('applicable_class_programs')->nullable()->after('applicable_grades');
            $table->json('applicable_types')->nullable()->after('applicable_class_programs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_fees', function (Blueprint $table) {
            $table->dropColumn(['applicable_grades', 'applicable_class_programs', 'applicable_types']);
        });
    }
};
