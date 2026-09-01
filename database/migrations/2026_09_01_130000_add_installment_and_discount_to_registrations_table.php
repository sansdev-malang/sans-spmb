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
            $table->decimal('discount_amount', 12, 2)->default(0)->after('final_fee_snapshot');
            $table->string('discount_notes', 255)->nullable()->after('discount_amount');
            $table->string('installment_mode', 20)->default('none')->after('discount_notes'); // 'none', 'all', 'selective'
            $table->json('installment_allowed_fee_ids')->nullable()->after('installment_mode');
            $table->decimal('min_installment_amount', 12, 2)->default(0)->after('installment_allowed_fee_ids');
            $table->foreignId('installment_approved_by')->nullable()->constrained('users')->nullOnDelete()->after('min_installment_amount');
            $table->timestamp('installment_approved_at')->nullable()->after('installment_approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropForeign(['installment_approved_by']);
            $table->dropColumn([
                'discount_amount',
                'discount_notes',
                'installment_mode',
                'installment_allowed_fee_ids',
                'min_installment_amount',
                'installment_approved_by',
                'installment_approved_at',
            ]);
        });
    }
};
