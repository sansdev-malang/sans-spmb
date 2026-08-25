<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_active',
        'settings_schema'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings_schema' => 'array'
    ];

    public function paymentChannels()
    {
        return $this->hasMany(SpmbPaymentChannel::class);
    }
}
