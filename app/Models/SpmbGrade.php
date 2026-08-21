<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbGrade extends Model
{
    protected $guarded = [];

    public function unit()
    {
        return $this->belongsTo(SpmbUnit::class, 'spmb_unit_id');
    }
}
