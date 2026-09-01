<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbFormField extends Model
{
    protected $guarded = [];

    public function step()
    {
        return $this->belongsTo(SpmbFormStep::class, 'form_step_id');
    }

    public function units()
    {
        return $this->belongsToMany(SpmbUnit::class, 'spmb_form_field_unit', 'spmb_form_field_id', 'spmb_unit_id');
    }
}
