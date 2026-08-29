<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbPaymentChannel extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'is_active',
        'payment_gateway_id'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function gateway()
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }

    /**
     * Get official transparent logo URL for the payment channel.
     */
    public function getLogoUrl()
    {
        $code = strtoupper($this->code);
        
        $logos = [
            'BCA' => 'https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg',
            'BNI' => 'https://upload.wikimedia.org/wikipedia/id/5/55/BNI_logo.svg',
            'BRI' => 'https://upload.wikimedia.org/wikipedia/commons/2/2e/BRI_Logo.svg',
            'MANDIRI' => 'https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg',
            'BSI' => 'https://upload.wikimedia.org/wikipedia/commons/a/a0/Bank_Syariah_Indonesia_2021.svg',
            'QRIS' => 'https://upload.wikimedia.org/wikipedia/commons/a/a2/Logo_QRIS.svg',
            'CIMB' => 'https://upload.wikimedia.org/wikipedia/commons/e/e0/CIMB_Niaga_logo.svg',
            'PERMATA' => 'https://upload.wikimedia.org/wikipedia/id/5/52/PermataBank_logo.svg',
            'SINARMAS' => 'https://upload.wikimedia.org/wikipedia/commons/a/a1/Logo_Bank_Sinarmas.svg',
            'MUAMALAT' => 'https://upload.wikimedia.org/wikipedia/id/2/2f/Bank_Muamalat_logo.svg',
            'BNC' => 'https://upload.wikimedia.org/wikipedia/commons/b/b3/Bank_Neo_Commerce_logo.svg',
            'OVO' => 'https://upload.wikimedia.org/wikipedia/commons/e/eb/Logo_ovo_purple.svg',
            'DANA' => 'https://upload.wikimedia.org/wikipedia/commons/7/72/Logo_DANA.svg',
            'SPAY' => 'https://upload.wikimedia.org/wikipedia/commons/f/fe/ShopeePay_Logo.svg',
            'ALFAMART' => 'https://upload.wikimedia.org/wikipedia/commons/8/86/Alfamart_logo.svg',
            'INDOMARET' => 'https://upload.wikimedia.org/wikipedia/commons/d/d3/Indomaret_logo.svg',
        ];

        foreach ($logos as $key => $url) {
            if (str_contains($code, $key)) {
                return $url;
            }
        }

        return null;
    }
}
