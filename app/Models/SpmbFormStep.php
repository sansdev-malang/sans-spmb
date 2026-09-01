<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbFormStep extends Model
{
    protected $guarded = [];

    public function fields()
    {
        return $this->hasMany(SpmbFormField::class, 'form_step_id')->orderBy('order');
    }

    public function units()
    {
        return $this->belongsToMany(SpmbUnit::class, 'spmb_form_step_unit', 'spmb_form_step_id', 'spmb_unit_id');
    }
}
