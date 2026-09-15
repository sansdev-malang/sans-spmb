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
        $step = SpmbFormStep::first();
        if ($step) {
            $existingField = SpmbFormField::where('field_name', 'info_source')->first();
            if (!$existingField) {
                $maxOrder = SpmbFormField::where('form_step_id', $step->id)->max('order') ?? 5;
                $field = SpmbFormField::create([
                    'form_step_id' => $step->id,
                    'field_name' => 'info_source',
                    'label' => 'Sumber Informasi Pendaftaran',
                    'type' => 'select',
                    'options' => 'Media Sosial (Instagram / FB / TikTok),Brosur / Spanduk / Baliho,Website Resmi Sekolah,Rekomendasi Wali Murid (Referral),Alumni / Keluarga Besar,Lainnya',
                    'is_required' => false,
                    'order' => $maxOrder + 1,
                ]);

                // Attach to all active units
                $units = SpmbUnit::all();
                if ($units->isNotEmpty() && method_exists($field, 'units')) {
                    $field->units()->sync($units->pluck('id'));
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SpmbFormField::where('field_name', 'info_source')->delete();
    }
};
