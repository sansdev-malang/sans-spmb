<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbPaymentChannel extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'logo',
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
