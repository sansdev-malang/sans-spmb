<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbFee extends Model
{
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(SpmbFeeCategory::class, 'spmb_fee_category_id');
    }

    public function unit()
    {
        return $this->belongsTo(SpmbUnit::class, 'spmb_unit_id');
    }
}
