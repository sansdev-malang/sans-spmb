<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SpmbFormField;
use App\Models\SpmbFormStep;
use App\Models\SpmbUnit;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create or ensure Step 7 exists at the end of the form (after Step 6 Data Lampiran)
        $step7 = SpmbFormStep::where('order', 7)->orWhere('title', 'like', '%Referral%')->first();
        if (!$step7) {
            $step7 = SpmbFormStep::create([
                'title' => 'Informasi & Referral',
                'order' => 7,
                'is_active' => true,
            ]);
        } else {
            $step7->update([
                'title' => 'Informasi & Referral',
                'order' => 7,
                'is_active' => true,
            ]);
        }

        // Attach Step 7 to all active units
        $units = SpmbUnit::all();
        if ($units->isNotEmpty() && method_exists($step7, 'units')) {
            $step7->units()->sync($units->pluck('id'));
        }

        // 2. Move info_source field to Step 7
        $field = SpmbFormField::where('field_name', 'info_source')->first();
        if ($field) {
            $field->update([
                'form_step_id' => $step7->id,
                'order' => 1,
                'label' => 'Saluran Informasi Pendaftaran',
                'type' => 'select',
            ]);
        } else {
            $field = SpmbFormField::create([
                'form_step_id' => $step7->id,
                'field_name' => 'info_source',
                'label' => 'Saluran Informasi Pendaftaran',
                'type' => 'select',
                'options' => 'Media Sosial,Brosur / Spanduk,Website Resmi,Rekomendasi Wali Murid (Referral),Alumni / Keluarga Besar,Lainnya',
                'is_required' => false,
                'order' => 1,
            ]);
        }

        if ($units->isNotEmpty() && method_exists($field, 'units')) {
            $field->units()->sync($units->pluck('id'));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $step1 = SpmbFormStep::where('order', 1)->first();
        $step7 = SpmbFormStep::where('order', 7)->first();

        if ($step1) {
            SpmbFormField::where('field_name', 'info_source')->update([
                'form_step_id' => $step1->id,
                'order' => 6,
            ]);
        }

        if ($step7) {
            $step7->delete();
        }
    }
};
