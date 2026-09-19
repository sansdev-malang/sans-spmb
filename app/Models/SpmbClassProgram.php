<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbClassProgram extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_testing' => 'boolean',
    ];

    public function scopeLive($query)
    {
        return $query->where($this->qualifyColumn('is_testing'), false);
    }

    public function scopeTrash($query)
    {
        return $query->where($this->qualifyColumn('is_testing'), true);
    }

    public function units()
    {
        return $this->belongsToMany(SpmbUnit::class, 'spmb_class_program_unit', 'spmb_class_program_id', 'spmb_unit_id')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function scopeForUnit($query, $unitId)
    {
        return $query->whereHas('units', function ($q) use ($unitId) {
            $q->where('spmb_units.id', $unitId)->where('spmb_class_program_unit.is_active', true);
        });
    }
}
