<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payment_info' => 'array',
    ];

    public function scopeScopedByAdmin($query)
    {
        if (auth()->check() && auth()->user()->spmb_unit_id) {
            return $query->whereHas('registration', function ($q) {
                $q->where('spmb_unit_id', auth()->user()->spmb_unit_id);
            });
        }
        return $query;
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * Get user-friendly payment channel name (e.g. 'VA MANDIRI', 'QRIS', 'SHOPEEPAY')
     */
    public function getChannelDisplayNameAttribute()
    {
        $info = is_array($this->payment_info) ? $this->payment_info : [];

        // 1. Check direct bank name in payment_info (Virtual Accounts)
        if (!empty($info['bankName'])) {
            $b = strtoupper($info['bankName']);
            if (in_array($b, ['MANDIRI', 'BRI', 'BNI', 'BCA', 'BSI', 'PERMATA', 'CIMB'])) {
                return 'VA ' . $b;
            }
            return $b;
        }

        // 2. Check channel in additionalInfo
        if (!empty($info['additionalInfo']['channel'])) {
            return strtoupper($info['additionalInfo']['channel']);
        }

        // 3. Check QRIS
        if (!empty($info['qrisUrl']) || !empty($info['qrContent']) || (strtolower($this->payment_gateway_code ?? '') === 'qris')) {
            return 'QRIS';
        }

        // 4. Check e-wallet
        if (!empty($info['ewalletChannel'])) {
            return strtoupper($info['ewalletChannel']);
        }

        // 5. Fallback to gateway code (e.g. WINPAY, BNI)
        return strtoupper($this->payment_gateway_code ?? 'Winpay');
    }
}
