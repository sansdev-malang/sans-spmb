<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbUnit extends Model
{
    protected $guarded = [];

    public function grades()
    {
        return $this->hasMany(SpmbGrade::class);
    }

    public function feeCategories()
    {
        return $this->belongsToMany(SpmbFeeCategory::class, 'spmb_fee_category_unit', 'spmb_unit_id', 'spmb_fee_category_id');
    }

    public function agreementTemplate()
    {
        return $this->hasOne(SpmbAgreementTemplate::class, 'spmb_unit_id');
    }

    /**
     * Get clean normalized WhatsApp number (starts with 62)
     */
    public function getCleanWhatsappNumberAttribute()
    {
        $phone = preg_replace('/[^0-9]/', '', $this->whatsapp_number ?? '');
        if (empty($phone)) {
            $phone = preg_replace('/[^0-9]/', '', Setting::get('spmb_whatsapp_general', '081234567890'));
        }
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }
        return $phone;
    }

    /**
     * Generate dynamic WhatsApp chat URL with prefilled text
     */
    public function getWhatsappUrl($candidateName = null, $regNumber = null)
    {
        $phone = $this->clean_whatsapp_number;
        $unitName = $this->name ?? 'Sekolah Anak Saleh';
        
        $text = "Halo Admin SPMB {$unitName}, saya ingin berkonsultasi mengenai pendaftaran SPMB";
        if ($candidateName) {
            $text .= " untuk ananda {$candidateName}";
        }
        if ($regNumber) {
            $text .= " (No. Registrasi: {$regNumber})";
        }
        $text .= ". Mohon informasinya. Terima kasih.";

        return "https://wa.me/{$phone}?text=" . urlencode($text);
    }
}
