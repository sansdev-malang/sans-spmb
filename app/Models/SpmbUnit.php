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
}
