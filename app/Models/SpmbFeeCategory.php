<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbFeeCategory extends Model
{
    protected $guarded = [];

    public function fees()
    {
        return $this->hasMany(SpmbFee::class, 'spmb_fee_category_id');
    }

    public function units()
    {
        return $this->belongsToMany(SpmbUnit::class, 'spmb_fee_category_unit', 'spmb_fee_category_id', 'spmb_unit_id');
    }
}
