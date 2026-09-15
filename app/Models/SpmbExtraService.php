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

    public function units()
    {
        return $this->belongsToMany(SpmbUnit::class, 'spmb_extra_service_unit', 'spmb_extra_service_id', 'spmb_unit_id')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function scopeForUnit($query, $unitId)
    {
        return $query->where(function($q) use ($unitId) {
            $q->whereHas('units', function ($sub) use ($unitId) {
                $sub->where('spmb_units.id', $unitId)->where('spmb_extra_service_unit.is_active', true);
            })->orWhere(function($sub) use ($unitId) {
                $sub->where('spmb_unit_id', $unitId)->where('is_active', true);
            });
        });
    }
}
