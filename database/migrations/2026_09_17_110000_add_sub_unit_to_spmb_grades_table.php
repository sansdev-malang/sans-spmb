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
        Schema::table('spmb_grades', function (Blueprint $table) {
            if (!Schema::hasColumn('spmb_grades', 'sub_unit')) {
                $table->string('sub_unit', 100)->nullable()->after('spmb_unit_id')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_grades', function (Blueprint $table) {
            if (Schema::hasColumn('spmb_grades', 'sub_unit')) {
                $table->dropColumn('sub_unit');
            }
        });
    }
};
