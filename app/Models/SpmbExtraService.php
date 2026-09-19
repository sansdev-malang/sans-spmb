<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbExtraService extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_testing' => 'boolean',
        'applicable_types' => 'array',
        'applicable_class_programs' => 'array',
        'applicable_waves' => 'array',
        'applicable_periods' => 'array',
        'applicable_grades' => 'array',
    ];

    public function scopeLive($query)
    {
        return $query->where('is_testing', false);
    }

    public function scopeTrash($query)
    {
        return $query->where('is_testing', true);
    }

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
        return $query->where('is_active', true)
            ->where(function($q) use ($unitId) {
                $q->whereHas('units', function ($sub) use ($unitId) {
                    $sub->where('spmb_units.id', $unitId)->where('spmb_extra_service_unit.is_active', true);
                })->orWhere(function($sub) use ($unitId) {
                    $sub->whereDoesntHave('units', function($u) use ($unitId) {
                        $u->where('spmb_units.id', $unitId);
                    })
                    ->where(function($unitQuery) use ($unitId) {
                        $unitQuery->whereNull('spmb_unit_id')->orWhere('spmb_unit_id', $unitId);
                    });
                });
            });
    }

    /**
     * Check if this extra service is eligible for the given criteria (Type, Class Program, Wave, Period, Grade).
     * If an applicable array is null or empty, it matches all.
     */
    public function matchesEligibility($typeId = null, $classProgramId = null, $waveId = null, $periodId = null, $gradeId = null): bool
    {
        if ($typeId !== null && !empty($this->applicable_types)) {
            if (!in_array((int)$typeId, array_map('intval', (array)$this->applicable_types))) {
                return false;
            }
        }

        if ($classProgramId !== null && !empty($this->applicable_class_programs)) {
            if (!in_array((int)$classProgramId, array_map('intval', (array)$this->applicable_class_programs))) {
                return false;
            }
        }

        if ($waveId !== null && !empty($this->applicable_waves)) {
            if (!in_array((int)$waveId, array_map('intval', (array)$this->applicable_waves))) {
                return false;
            }
        }

        if ($periodId !== null && !empty($this->applicable_periods)) {
            if (!in_array((int)$periodId, array_map('intval', (array)$this->applicable_periods))) {
                return false;
            }
        }

        if ($gradeId !== null && !empty($this->applicable_grades)) {
            if (!in_array((int)$gradeId, array_map('intval', (array)$this->applicable_grades))) {
                return false;
            }
        }

        return true;
    }
}
