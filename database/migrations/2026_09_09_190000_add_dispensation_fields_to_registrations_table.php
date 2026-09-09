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
        Schema::table('registrations', function (Blueprint $table) {
            $table->boolean('is_dispensation')->default(false)->after('installment_approved_at');
            $table->string('dispensation_reason', 100)->nullable()->after('is_dispensation');
            $table->foreignId('dispensation_approved_by')->nullable()->constrained('users')->nullOnDelete()->after('dispensation_reason');
            $table->timestamp('dispensation_approved_at')->nullable()->after('dispensation_approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropForeign(['dispensation_approved_by']);
            $table->dropColumn([
                'is_dispensation',
                'dispensation_reason',
                'dispensation_approved_by',
                'dispensation_approved_at',
            ]);
        });
    }
};
