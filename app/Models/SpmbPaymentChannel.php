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
}
