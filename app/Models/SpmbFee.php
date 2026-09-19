<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbFee extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payment_gateway' => 'array',
        'is_active' => 'boolean',
        'is_testing' => 'boolean',
        'applicable_grades' => 'array',
        'applicable_class_programs' => 'array',
        'applicable_types' => 'array',
        'applicable_periods' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(SpmbFeeCategory::class, 'spmb_fee_category_id');
    }

    public function unit()
    {
        return $this->belongsTo(SpmbUnit::class, 'spmb_unit_id');
    }

    public function scopeLive($query)
    {
        return $query->where($this->qualifyColumn('is_testing'), false);
    }

    public function scopeTrash($query)
    {
        return $query->where($this->qualifyColumn('is_testing'), true);
    }

    /**
     * Check if this fee component matches a candidate registration
     */
    public function matchesRegistration($registration): bool
    {
        if (!$registration) {
            return false;
        }

        // 0. Testing fee protection: testing fees are never applied to regular registrations
        if ($this->is_testing) {
            return false;
        }

        // 1. Period / Academic Year match (Strict: if empty/not set, fee is inactive/not used)
        if (empty($this->applicable_periods) || !is_array($this->applicable_periods)) {
            return false;
        }
        $periodIds = array_map('intval', $this->applicable_periods);
        if (!$registration->spmb_period_id || !in_array((int)$registration->spmb_period_id, $periodIds, true)) {
            return false;
        }

        // 2. Category Match (Period & Unit of the parent category)
        if ($this->category) {
            if (!$this->category->matchesPeriod($registration->spmb_period_id)) {
                return false;
            }
            if ($this->category->units && $this->category->units->isNotEmpty() && $registration->spmb_unit_id) {
                $catUnitIds = $this->category->units->pluck('id')->map(fn($id) => (int)$id)->toArray();
                if (!in_array((int)$registration->spmb_unit_id, $catUnitIds, true)) {
                    return false;
                }
            }
        }

        // 3. Fee Unit match
        if ($this->spmb_unit_id && (int)$this->spmb_unit_id !== (int)$registration->spmb_unit_id) {
            return false;
        }

        // 4. Grade match (Target Kelas)
        if (!empty($this->applicable_grades) && is_array($this->applicable_grades)) {
            $gradeIds = array_map('intval', $this->applicable_grades);
            $matchesPrimary = $registration->spmb_grade_id && in_array((int)$registration->spmb_grade_id, $gradeIds, true);
            $matchesSecondary = $registration->spmb_secondary_grade_id && in_array((int)$registration->spmb_secondary_grade_id, $gradeIds, true);
            if (!$matchesPrimary && !$matchesSecondary) {
                return false;
            }
        } elseif (empty($this->applicable_grades)) {
            // Dynamic fallback: if targeting checklist is not explicitly set, match by grade keyword if present in fee name
            $gradeName = $registration->grade->name ?? '';
            $secGradeName = $registration->secondaryGrade->name ?? '';
            $feeNameUpper = strtoupper($this->name);
            $gradeNameUpper = strtoupper($gradeName);
            $secGradeNameUpper = strtoupper($secGradeName);

            // Fetch all active grade names dynamically from master database table
            static $dbGradeKeywords = null;
            if ($dbGradeKeywords === null) {
                $dbGradeKeywords = SpmbGrade::pluck('name')
                    ->filter()
                    ->map(fn($n) => strtoupper(trim($n)))
                    ->unique()
                    ->values()
                    ->toArray();
            }

            $hasOtherGradeKeyword = false;
            $normalizedGrade = str_replace('-', ' ', $gradeNameUpper);
            $normalizedSecGrade = str_replace('-', ' ', $secGradeNameUpper);

            foreach ($dbGradeKeywords as $kw) {
                if (empty($kw)) continue;
                $normalizedKw = str_replace('-', ' ', $kw);
                if (str_contains($feeNameUpper, $kw) || str_contains($feeNameUpper, $normalizedKw)) {
                    if (
                        (!empty($gradeNameUpper) && (str_contains($gradeNameUpper, $kw) || str_contains($normalizedGrade, $normalizedKw)))
                        || (!empty($secGradeNameUpper) && (str_contains($secGradeNameUpper, $kw) || str_contains($normalizedSecGrade, $normalizedKw)))
                    ) {
                        $hasOtherGradeKeyword = false;
                        break;
                    } else {
                        $hasOtherGradeKeyword = true;
                    }
                }
            }

            if ($hasOtherGradeKeyword) {
                return false;
            }
        }

        // 5. Class Program / Category match (Target Kategori Murid: Reguler, Inklusi, ICP, dsb)
        if (!empty($this->applicable_class_programs) && is_array($this->applicable_class_programs)) {
            $progIds = array_map('intval', $this->applicable_class_programs);
            if (!$registration->spmb_class_program_id || !in_array((int)$registration->spmb_class_program_id, $progIds, true)) {
                return false;
            }
        }

        // 6. Registration Type / Jalur match (Target Jalur Pendaftaran: Inden, Reguler, Prestasi, dsb)
        if (!empty($this->applicable_types) && is_array($this->applicable_types)) {
            $typeIds = array_map('intval', $this->applicable_types);
            if (!$registration->spmb_type_id || !in_array((int)$registration->spmb_type_id, $typeIds, true)) {
                return false;
            }
        }

        // 7. Gender match (Laki-laki / Perempuan)
        $regGender = strtolower(trim($registration->gender ?? ''));
        if (!empty($regGender)) {
            $isCandidateMale = in_array($regGender, ['l', 'laki-laki', 'male', 'ikhwan', 'putra'], true);
            $isCandidateFemale = in_array($regGender, ['p', 'perempuan', 'female', 'akhwat', 'putri'], true);

            // 1. Explicit applicable_gender configured on fee
            if (!empty($this->applicable_gender) && $this->applicable_gender !== 'all') {
                $feeGender = strtolower(trim($this->applicable_gender));
                if (in_array($feeGender, ['male', 'laki-laki', 'l', 'putra', 'ikhwan'], true)) {
                    if (!$isCandidateMale) return false;
                } elseif (in_array($feeGender, ['female', 'perempuan', 'p', 'putri', 'akhwat'], true)) {
                    if (!$isCandidateFemale) return false;
                }
            } else {
                // 2. Keyword check on fee name (e.g. "Perlengkapan - Laki-Laki", "Seragam Putri")
                $feeNameLower = strtolower($this->name);
                $hasMaleKw = str_contains($feeNameLower, 'laki-laki') || str_contains($feeNameLower, 'laki') || str_contains($feeNameLower, 'putra') || str_contains($feeNameLower, 'ikhwan');
                $hasFemaleKw = str_contains($feeNameLower, 'perempuan') || str_contains($feeNameLower, 'putri') || str_contains($feeNameLower, 'akhwat');

                if ($hasMaleKw && !$hasFemaleKw && !$isCandidateMale) {
                    return false;
                }
                if ($hasFemaleKw && !$hasMaleKw && !$isCandidateFemale) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get readable target grades label
     */
    public function getTargetGradesTextAttribute(): string
    {
        if (empty($this->applicable_grades) || !is_array($this->applicable_grades)) {
            return 'Semua Kelas';
        }
        $names = SpmbGrade::whereIn('id', $this->applicable_grades)->pluck('name')->toArray();
        return !empty($names) ? implode(', ', $names) : 'Semua Kelas';
    }

    /**
     * Get readable target class programs label
     */
    public function getTargetClassProgramsTextAttribute(): string
    {
        if (empty($this->applicable_class_programs) || !is_array($this->applicable_class_programs)) {
            return 'Semua Kategori';
        }
        $names = SpmbClassProgram::whereIn('id', $this->applicable_class_programs)->pluck('name')->toArray();
        return !empty($names) ? implode(', ', $names) : 'Semua Kategori';
    }

    /**
     * Get readable target types label
     */
    public function getTargetTypesTextAttribute(): string
    {
        if (empty($this->applicable_types) || !is_array($this->applicable_types)) {
            return 'Semua Jalur';
        }
        $names = SpmbType::whereIn('id', $this->applicable_types)->pluck('name')->toArray();
        return !empty($names) ? implode(', ', $names) : 'Semua Jalur';
    }

    /**
     * Get readable target periods label
     */
    public function getTargetPeriodsTextAttribute(): string
    {
        if (empty($this->applicable_periods) || !is_array($this->applicable_periods)) {
            return 'Tidak Ada (Non-Aktif)';
        }
        $names = SpmbPeriod::whereIn('id', $this->applicable_periods)->orderBy('year', 'desc')->pluck('year')->toArray();
        return !empty($names) ? implode(', ', $names) : 'Tidak Ada (Non-Aktif)';
    }

    /**
     * Get readable target gender label
     */
    public function getTargetGenderTextAttribute(): string
    {
        $g = strtolower(trim($this->applicable_gender ?? ''));
        if (in_array($g, ['male', 'laki-laki', 'l', 'putra'], true)) {
            return 'Laki-laki (Putra)';
        } elseif (in_array($g, ['female', 'perempuan', 'p', 'putri'], true)) {
            return 'Perempuan (Putri)';
        }
        return 'Semua Gender';
    }
}
