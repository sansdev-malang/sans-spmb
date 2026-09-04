<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbPaymentChannel extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'fee_type',
        'fee_value',
        'logo',
        'is_active',
        'payment_gateway_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'fee_value' => 'float',
    ];

    public function gateway()
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }

    /**
     * Calculate dynamic transaction fee for a given base amount.
     */
    public function calculateFee(float $baseAmount): float
    {
        if ($this->fee_type === 'percent') {
            return (float) round($baseAmount * (($this->fee_value ?? 0) / 100));
        }

        return (float) round($this->fee_value ?? 0);
    }

    /**
     * Get human-readable fee badge label (e.g. 'Rp 4.500 (Flat)' or '0.7% (MDR)').
     */
    public function getFeeLabelAttribute(): string
    {
        if ($this->fee_type === 'percent') {
            $val = rtrim(rtrim(number_format($this->fee_value, 2, '.', ''), '0'), '.');
            return "{$val}% (MDR)";
        }

        return 'Rp ' . number_format($this->fee_value, 0, ',', '.') . ' (Flat)';
    }

    /**
     * Get logo URL from storage.
     */
    public function getLogoUrl()
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        return null;
    }
}
