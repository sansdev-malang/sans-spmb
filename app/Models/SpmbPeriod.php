<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SpmbPeriod extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function units()
    {
        return $this->belongsToMany(SpmbUnit::class, 'spmb_period_unit', 'spmb_period_id', 'spmb_unit_id')
            ->withPivot('is_active', 'is_default')
            ->withTimestamps();
    }

    public function scopeForUnit($query, $unitId)
    {
        return $query->whereHas('units', function ($q) use ($unitId) {
            $q->where('spmb_units.id', $unitId)->where('spmb_period_unit.is_active', true);
        });
    }

    /**
     * Smartly resolve the default period ID for a unit or system globally
     */
    public static function getDefaultPeriodId($unitId = null)
    {
        if ($unitId === null && auth()->check() && !auth()->user()->isSuperAdmin()) {
            $unitId = auth()->user()->spmb_unit_id;
        }

        if ($unitId) {
            $unitDefault = DB::table('spmb_period_unit')
                ->where('spmb_unit_id', $unitId)
                ->where('is_default', true)
                ->value('spmb_period_id');
            if ($unitDefault) {
                return (int)$unitDefault;
            }

            $unitActive = DB::table('spmb_period_unit')
                ->where('spmb_unit_id', $unitId)
                ->where('is_active', true)
                ->orderBy('spmb_period_id', 'desc')
                ->value('spmb_period_id');
            if ($unitActive) {
                return (int)$unitActive;
            }
        }

        // Global default
        $globalDefault = static::where('is_default', true)->value('id');
        if ($globalDefault) {
            return (int)$globalDefault;
        }

        return (int)(static::where('is_active', true)->orderBy('id', 'desc')->value('id')
            ?? static::orderBy('id', 'desc')->value('id')
            ?? 1);
    }

    /**
     * Backward-compatible alias for getActivePeriodId
     */
    public static function getActivePeriodId($unitId = null)
    {
        return static::getDefaultPeriodId($unitId);
    }

    /**
     * Set a period as the default system / unit period
     */
    public static function setDefaultPeriod($periodId, $unitId = null)
    {
        if ($unitId) {
            DB::table('spmb_period_unit')
                ->where('spmb_unit_id', $unitId)
                ->update(['is_default' => false]);

            DB::table('spmb_period_unit')
                ->updateOrInsert(
                    ['spmb_period_id' => $periodId, 'spmb_unit_id' => $unitId],
                    ['is_default' => true, 'is_active' => true, 'updated_at' => now()]
                );
        }

        // Global system default
        static::query()->update(['is_default' => false]);
        static::where('id', $periodId)->update(['is_default' => true, 'is_active' => true]);

        // Also ensure pivot has is_default true across all units if global
        if (!$unitId) {
            DB::table('spmb_period_unit')->update(['is_default' => false]);
            DB::table('spmb_period_unit')->where('spmb_period_id', $periodId)->update(['is_default' => true, 'is_active' => true]);
        }
    }
}
