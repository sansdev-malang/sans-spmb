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
}
