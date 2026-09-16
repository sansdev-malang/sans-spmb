<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbPeriod extends Model
{
    protected $guarded = [];

    public function units()
    {
        return $this->belongsToMany(SpmbUnit::class, 'spmb_period_unit', 'spmb_period_id', 'spmb_unit_id')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function scopeForUnit($query, $unitId)
    {
        return $query->whereHas('units', function ($q) use ($unitId) {
            $q->where('spmb_units.id', $unitId)->where('spmb_period_unit.is_active', true);
        });
    }

    public static function getActivePeriodId()
    {
        return session('selected_period_id', function() {
            return static::where('is_active', true)->orderBy('id', 'desc')->value('id')
                ?? static::orderBy('id', 'desc')->value('id');
        });
    }
}
