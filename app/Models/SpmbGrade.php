<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SpmbGrade extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_testing' => 'boolean',
        'min_age_years' => 'integer',
        'min_age_months' => 'integer',
        'max_age_years' => 'integer',
        'max_age_months' => 'integer',
        'applicable_types' => 'array',
        'applicable_class_programs' => 'array',
        'applicable_waves' => 'array',
        'applicable_periods' => 'array',
    ];

    public function scopeLive($query)
    {
        return $query->where('is_testing', false);
    }

    public function scopeTrash($query)
    {
        return $query->where('is_testing', true);
    }

    public function unit()
    {
        return $this->belongsTo(SpmbUnit::class, 'spmb_unit_id');
    }

    /**
     * Get minimum age in total months.
     */
    public function getMinAgeTotalMonthsAttribute(): ?int
    {
        if ($this->min_age_years === null) {
            return null;
        }
        return ($this->min_age_years * 12) + ($this->min_age_months ?? 0);
    }

    /**
     * Get maximum age in total months.
     */
    public function getMaxAgeTotalMonthsAttribute(): ?int
    {
        if ($this->max_age_years === null) {
            return null;
        }
        return ($this->max_age_years * 12) + ($this->max_age_months ?? 0);
    }

    /**
     * Get formatted age range label (e.g., "4 thn 0 bln - 5 thn 6 bln").
     */
    public function getAgeRangeLabelAttribute(): string
    {
        $hasMin = $this->min_age_years !== null;
        $hasMax = $this->max_age_years !== null;

        if (!$hasMin && !$hasMax) {
            return 'Tanpa Batasan';
        }

        $minStr = $hasMin ? "{$this->min_age_years} th" . ($this->min_age_months > 0 ? " {$this->min_age_months} bln" : " 0 bln") : null;
        $maxStr = $hasMax ? "{$this->max_age_years} th" . ($this->max_age_months > 0 ? " {$this->max_age_months} bln" : " 0 bln") : null;

        if ($hasMin && $hasMax) {
            return "{$minStr} - {$maxStr}";
        } elseif ($hasMin) {
            return "Min. {$minStr}";
        } else {
            return "Maks. {$maxStr}";
        }
    }

    /**
     * Resolve target cut-off date (1 July of active period or current year).
     */
    public static function resolveCutoffDate($periodYear = null): Carbon
    {
        if (!empty($periodYear)) {
            // E.g. "2027/2028" or "2027-2028" -> take first year 2027
            if (preg_match('/^(\d{4})/', $periodYear, $matches)) {
                return Carbon::create((int)$matches[1], 7, 1)->startOfDay();
            }
        }
        
        // Check active period in database
        $activePeriod = SpmbPeriod::where('is_active', true)->first();
        if ($activePeriod && preg_match('/^(\d{4})/', $activePeriod->year, $matches)) {
            return Carbon::create((int)$matches[1], 7, 1)->startOfDay();
        }

        return Carbon::create((int)date('Y'), 7, 1)->startOfDay();
    }

    /**
     * Calculate age breakdown in years and months against cut-off date.
     */
    public static function calculateAge($birthDate, $targetDate = null): array
    {
        if (empty($birthDate)) {
            return ['years' => 0, 'months' => 0, 'total_months' => 0, 'text' => '-'];
        }

        $birth = Carbon::parse($birthDate)->startOfDay();
        $target = $targetDate ? Carbon::parse($targetDate)->startOfDay() : self::resolveCutoffDate();

        if ($birth->gt($target)) {
            return ['years' => 0, 'months' => 0, 'total_months' => 0, 'text' => '0 Tahun 0 Bulan'];
        }

        $diff = $birth->diff($target);
        $years = $diff->y;
        $months = $diff->m;
        $totalMonths = ($years * 12) + $months;

        return [
            'years' => $years,
            'months' => $months,
            'days' => $diff->d,
            'total_months' => $totalMonths,
            'text' => "{$years} Tahun {$months} Bulan" . ($diff->d > 0 ? " {$diff->d} Hari" : ""),
            'short_text' => "{$years} thn {$months} bln",
        ];
    }

    /**
     * Validate a birth date against this grade's age limits.
     */
    public function validateAge($birthDate, $targetDate = null): array
    {
        $hasMin = $this->min_age_years !== null;
        $hasMax = $this->max_age_years !== null;

        if (!$hasMin && !$hasMax) {
            return ['valid' => true, 'message' => null, 'age' => self::calculateAge($birthDate, $targetDate)];
        }

        $age = self::calculateAge($birthDate, $targetDate);
        $totalMonths = $age['total_months'];

        $minMonths = $this->min_age_total_months;
        $maxMonths = $this->max_age_total_months;

        if ($hasMin && $totalMonths < $minMonths) {
            $minStr = "{$this->min_age_years} tahun" . ($this->min_age_months > 0 ? " {$this->min_age_months} bulan" : "");
            $msg = "Usia calon murid saat ini ({$age['short_text']}) belum memenuhi batas minimal untuk {$this->name} (minimal {$minStr} per 1 Juli).";
            if (!empty($this->age_notes)) {
                $msg .= " Catatan: {$this->age_notes}";
            }
            return ['valid' => false, 'message' => $msg, 'age' => $age];
        }

        if ($hasMax && $totalMonths > $maxMonths) {
            $maxStr = "{$this->max_age_years} tahun" . ($this->max_age_months > 0 ? " {$this->max_age_months} bulan" : "");
            $msg = "Usia calon murid saat ini ({$age['short_text']}) melebihi batas maksimal untuk {$this->name} (maksimal {$maxStr} per 1 Juli).";
            if (!empty($this->age_notes)) {
                $msg .= " Catatan: {$this->age_notes}";
            }
            return ['valid' => false, 'message' => $msg, 'age' => $age];
        }

        return ['valid' => true, 'message' => null, 'age' => $age];
    }

    /**
     * Check if this grade is eligible for the given criteria (Type, Class Program, Wave, Period).
     * If an applicable array is null or empty, it matches all.
     */
    public function matchesEligibility($typeId = null, $classProgramId = null, $waveId = null, $periodId = null): bool
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

        return true;
    }
}
