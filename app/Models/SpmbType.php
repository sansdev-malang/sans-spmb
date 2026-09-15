<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbType extends Model
{
    protected $guarded = [];

    public function units()
    {
        return $this->belongsToMany(SpmbUnit::class, 'spmb_type_unit', 'spmb_type_id', 'spmb_unit_id')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function scopeForUnit($query, $unitId)
    {
        return $query->whereHas('units', function ($q) use ($unitId) {
            $q->where('spmb_units.id', $unitId)->where('spmb_type_unit.is_active', true);
        });
    }
}
