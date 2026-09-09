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
        Schema::table('spmb_fee_categories', function (Blueprint $table) {
            $table->string('category_type', 50)->default('tuition_fee')->after('name');
        });

        // Initialize existing category types intelligently
        $categories = DB::table('spmb_fee_categories')->get();
        foreach ($categories as $cat) {
            $nameLower = strtolower(trim($cat->name));
            if (preg_match('/(formulir|pendaftaran|registrasi|enrollment|registration)/i', $nameLower) || $cat->id == 1) {
                DB::table('spmb_fee_categories')->where('id', $cat->id)->update(['category_type' => 'registration_fee']);
            } elseif (preg_match('/(tambahan|extra|layanan|tpa|tpq)/i', $nameLower) || $cat->id == 3) {
                DB::table('spmb_fee_categories')->where('id', $cat->id)->update(['category_type' => 'extra_service']);
            } else {
                DB::table('spmb_fee_categories')->where('id', $cat->id)->update(['category_type' => 'tuition_fee']);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spmb_fee_categories', function (Blueprint $table) {
            $table->dropColumn('category_type');
        });
    }
};
