<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbFormStep extends Model
{
    protected $fillable = ['title', 'order', 'is_active', 'spmb_unit_id'];

    public function fields()
    {
        return $this->hasMany(SpmbFormField::class, 'form_step_id')->orderBy('order');
    }

    public function unit()
    {
        return $this->belongsTo(SpmbUnit::class, 'spmb_unit_id');
    }
}
