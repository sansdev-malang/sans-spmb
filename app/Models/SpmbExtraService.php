<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbExtraService extends Model
{
    protected $guarded = [];

    /**
     * Get the unit associated with the extra service (nullable for general/all units).
     */
    public function unit()
    {
        return $this->belongsTo(SpmbUnit::class, 'spmb_unit_id');
    }

    /**
     * Get the registrations associated with the extra service.
     */
    public function registrations()
    {
        return $this->belongsToMany(Registration::class, 'registration_extra_service', 'spmb_extra_service_id', 'registration_id');
    }
}
