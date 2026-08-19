<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbFormField extends Model
{
    protected $fillable = ['form_step_id', 'label', 'field_name', 'type', 'options', 'is_required', 'order'];

    public function step()
    {
        return $this->belongsTo(SpmbFormStep::class, 'form_step_id');
    }
}
