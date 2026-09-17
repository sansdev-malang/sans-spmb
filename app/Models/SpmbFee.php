<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbFee extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payment_gateway' => 'array',
        'is_active' => 'boolean',
        'applicable_grades' => 'array',
        'applicable_class_programs' => 'array',
        'applicable_types' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(SpmbFeeCategory::class, 'spmb_fee_category_id');
    }

    public function unit()
    {
        return $this->belongsTo(SpmbUnit::class, 'spmb_unit_id');
    }

    /**
     * Check if this fee component matches a candidate registration
     */
    public function matchesRegistration($registration): bool
    {
        if (!$registration) {
            return false;
        }

        // Unit match
        if ($this->spmb_unit_id && (int)$this->spmb_unit_id !== (int)$registration->spmb_unit_id) {
            return false;
        }

        // Grade match
        if (!empty($this->applicable_grades) && is_array($this->applicable_grades)) {
            $gradeIds = array_map('intval', $this->applicable_grades);
            $matchesPrimary = $registration->spmb_grade_id && in_array((int)$registration->spmb_grade_id, $gradeIds, true);
            $matchesSecondary = $registration->spmb_secondary_grade_id && in_array((int)$registration->spmb_secondary_grade_id, $gradeIds, true);
            if (!$matchesPrimary && !$matchesSecondary) {
                return false;
            }
        } elseif (empty($this->applicable_grades)) {
            // Backward-compatibility: if targeting not explicitly set, match by grade keyword if present in fee name
            $gradeName = $registration->grade->name ?? '';
            $secGradeName = $registration->secondaryGrade->name ?? '';
            $feeNameUpper = strtoupper($this->name);
            $gradeNameUpper = strtoupper($gradeName);
            $secGradeNameUpper = strtoupper($secGradeName);

            $allGradeKeywords = [
                'TPA 1', 'TPA 2', 'TPA 3', 'TPA',
                'KB-A', 'KB-B', 'KB A', 'KB B', 'KB',
                'TK-A', 'TK-B', 'TK A', 'TK B', 'TK',
                'KELAS 1', 'KELAS 2', 'KELAS 3', 'KELAS 4', 'KELAS 5', 'KELAS 6',
                'KELAS 7', 'KELAS 8', 'KELAS 9'
            ];
            $hasOtherGradeKeyword = false;

            $normalizedGrade = str_replace('-', ' ', $gradeNameUpper);
            $normalizedSecGrade = str_replace('-', ' ', $secGradeNameUpper);

            foreach ($allGradeKeywords as $kw) {
                if (str_contains($feeNameUpper, $kw)) {
                    $normalizedKw = str_replace('-', ' ', $kw);
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

        // Class Program / Category match
        if (!empty($this->applicable_class_programs) && is_array($this->applicable_class_programs)) {
            $progIds = array_map('intval', $this->applicable_class_programs);
            if ($registration->spmb_class_program_id && !in_array((int)$registration->spmb_class_program_id, $progIds, true)) {
                return false;
            }
        }

        // Registration Type / Jalur match
        if (!empty($this->applicable_types) && is_array($this->applicable_types)) {
            $typeIds = array_map('intval', $this->applicable_types);
            if ($registration->spmb_type_id && !in_array((int)$registration->spmb_type_id, $typeIds, true)) {
                return false;
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
}
