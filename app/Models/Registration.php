<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $guarded = [];

    protected $casts = [
        'birth_date' => 'date',
        'observation_date' => 'date',
        'additional_info' => 'array',
        'final_fee_snapshot' => 'array',
        'invalid_fields' => 'array',
        'signed_at' => 'datetime',
        'installment_allowed_fee_ids' => 'array',
        'item_discounts' => 'array',
        'installment_approved_at' => 'datetime',
        'discount_amount' => 'float',
        'min_installment_amount' => 'float',
        'is_dispensation' => 'boolean',
        'is_testing' => 'boolean',
        'dispensation_approved_at' => 'datetime',
        'observation_attendance_confirmed_at' => 'datetime',
        'observation_result_uploaded_at' => 'datetime',
    ];

    protected $appends = [
        'registration_fee_name',
        'id_label',
    ];

    public function scopeLive($query)
    {
        return $query->where($this->qualifyColumn('is_testing'), false);
    }

    public function scopeTrash($query)
    {
        return $query->where($this->qualifyColumn('is_testing'), true);
    }

    public function scopeScopedByAdmin($query)
    {
        if (auth()->check() && auth()->user()->spmb_unit_id) {
            return $query->where($this->qualifyColumn('spmb_unit_id'), auth()->user()->spmb_unit_id);
        }
        return $query;
    }

    public function getFieldValue($fieldName)
    {
        if ($fieldName === 'class_program') {
            return $this->classProgram->name ?? null;
        }

        if ($fieldName === 'extra_services') {
            return $this->extraServices->pluck('name')->implode(', ') ?: null;
        }

        if ($fieldName === 'id_label') {
            return $this->id_label;
        }

        $columns = [
            'candidate_name', 'nickname', 'nik', 'family_card_no', 'gender', 'birth_place', 
            'birth_date', 'religion', 'previous_school', 'admission_level',
            'address', 'house_number', 'rt', 'rw', 'kelurahan', 'kecamatan', 'city', 'province',
            'father_name', 'father_nik', 'father_job', 'father_address', 'father_phone',
            'mother_name', 'mother_nik', 'mother_job', 'mother_address', 'mother_phone',
            'guardian_name', 'guardian_nik', 'guardian_job', 'guardian_address', 'guardian_phone', 'parent_phone',
            'student_photo_path', 'birth_certificate_path', 'family_card_path', 'diploma_certificate_path',
            'student_card_path', 'special_needs_assessment_path', 'payment_receipt_path',
            'spmb_wave_id', 'spmb_type_id', 'spmb_period_id', 'spmb_class_program_id',
            'observation_date', 'observation_time', 'observation_location', 'observation_room', 'observation_address', 'observation_interviewer', 'observation_notes',
            'observation_attendance_status', 'observation_attendance_notes', 'observation_attendance_confirmed_at',
            'observation_result_path', 'observation_result_notes', 'observation_result_uploaded_at'
        ];

        if (in_array($fieldName, $columns)) {
            $val = $this->{$fieldName};
            if ($fieldName === 'birth_date' && $val instanceof \DateTimeInterface) {
                return $val->format('Y-m-d');
            }
            if ($fieldName === 'parent_phone' && empty($val)) {
                return $this->father_phone ?? $this->mother_phone ?? $this->guardian_phone ?? null;
            }
            return $val;
        }

        return $this->additional_info[$fieldName] ?? null;
    }

    public function getFullCandidateAddress(): string
    {
        $parts = [];
        if (!empty($this->address)) {
            $parts[] = trim($this->address);
        }
        if (!empty($this->house_number)) {
            $parts[] = 'No. ' . trim($this->house_number);
        }
        if (!empty($this->rt) || !empty($this->rw)) {
            $rtRw = [];
            if (!empty($this->rt)) $rtRw[] = 'RT ' . trim($this->rt);
            if (!empty($this->rw)) $rtRw[] = 'RW ' . trim($this->rw);
            $parts[] = implode(' / ', $rtRw);
        }
        if (!empty($this->kelurahan)) {
            $parts[] = 'Kel. ' . trim($this->kelurahan);
        }
        if (!empty($this->kecamatan)) {
            $parts[] = 'Kec. ' . trim($this->kecamatan);
        }
        if (!empty($this->city)) {
            $parts[] = trim($this->city);
        }
        if (!empty($this->province)) {
            $parts[] = trim($this->province);
        }

        return implode(', ', $parts);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function activePayment()
    {
        return $this->hasOne(Payment::class)->whereIn('status', ['pending', 'success'])->latestOfMany();
    }

    public function activeRegistrationPayment()
    {
        return $this->hasOne(Payment::class)->where('payment_type', 'registration_fee')->whereIn('status', ['pending', 'success'])->latestOfMany();
    }

    public function activeFinalPayment()
    {
        return $this->hasOne(Payment::class)->where('payment_type', 'final_fee')->whereIn('status', ['pending', 'success'])->latestOfMany();
    }

    public function period()
    {
        return $this->belongsTo(SpmbPeriod::class, 'spmb_period_id');
    }

    public function wave()
    {
        return $this->belongsTo(SpmbWave::class, 'spmb_wave_id');
    }

    public function type()
    {
        return $this->belongsTo(SpmbType::class, 'spmb_type_id');
    }

    public function unit()
    {
        return $this->belongsTo(SpmbUnit::class, 'spmb_unit_id');
    }

    public function grade()
    {
        return $this->belongsTo(SpmbGrade::class, 'spmb_grade_id');
    }

    public function secondaryGrade()
    {
        return $this->belongsTo(SpmbGrade::class, 'spmb_secondary_grade_id');
    }

    public function classProgram()
    {
        return $this->belongsTo(SpmbClassProgram::class, 'spmb_class_program_id');
    }

    public function extraServices()
    {
        return $this->belongsToMany(SpmbExtraService::class, 'registration_extra_service', 'registration_id', 'spmb_extra_service_id');
    }

    public function installmentApprover()
    {
        return $this->belongsTo(User::class, 'installment_approved_by');
    }

    public function dispensationApprover()
    {
        return $this->belongsTo(User::class, 'dispensation_approved_by');
    }

    /**
     * Get official ID Pendaftaran label matching admin formatting (e.g. SANS-2027-0012)
     */
    public function getIdLabelAttribute()
    {
        $year = substr($this->period->year ?? date('Y'), 0, 4);
        return 'SANS-' . $year . '-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get complete list of final fee items for this candidate
     * Dynamically resolved from master Tarif & Biaya (SpmbFee) and merged with paid historical items
     */
    public function getFinalFeeDetails($forceLive = false)
    {
        $unitId = $this->spmb_unit_id;
        $gradeName = $this->grade->name ?? '';
        $extraServices = $this->extraServices ?? collect();

        // 1. Identify Registration Form Fee Category IDs to exclude
        $regCatIds = SpmbFeeCategory::where('category_type', SpmbFeeCategory::TYPE_REGISTRATION)
            ->orWhere(function($q) {
                $q->where('name', 'like', '%Formulir%')
                  ->orWhere('name', 'like', '%Pendaftaran%')
                  ->orWhere('name', 'like', '%Registrasi%')
                  ->orWhere('name', 'like', '%Enrollment%')
                  ->orWhere('name', 'like', '%Registration%');
            })->pluck('id')->toArray();

        // 2. Identify Extra Services / Biaya Tambahan Category IDs
        $extraCatIds = SpmbFeeCategory::where('category_type', SpmbFeeCategory::TYPE_EXTRA)
            ->orWhere(function($q) {
                $q->where('name', 'like', '%Tambahan%')
                  ->orWhere('name', 'like', '%Extra%')
                  ->orWhere('name', 'like', '%Layanan%');
            })->pluck('id')->toArray();

        // 3. Fetch active fees for this candidate's unit from master (or global null unit)
        $unitFeesQuery = SpmbFee::with('category')
            ->where(function($q) use ($unitId) {
                $q->where('spmb_unit_id', $unitId)
                  ->orWhereNull('spmb_unit_id');
            })
            ->where('is_active', true);

        if (!empty($regCatIds)) {
            $unitFeesQuery->whereNotIn('spmb_fee_category_id', $regCatIds);
        }

        $allUnitFees = $unitFeesQuery->get();
        $selectedMasterFees = collect();

        foreach ($allUnitFees as $fee) {
            $catType = $fee->category->category_type ?? null;
            $catName = strtolower(trim($fee->category->name ?? ''));
            $feeNameClean = strtolower(trim($fee->name ?? ''));

            // Never include registration / enrollment fee in final admission fees
            if ($catType === SpmbFeeCategory::TYPE_REGISTRATION
                || in_array($fee->spmb_fee_category_id, $regCatIds)
                || preg_match('/(formulir|pendaftaran|registrasi|enrollment|registration)/i', $feeNameClean)
                || preg_match('/(formulir|pendaftaran|registrasi|enrollment|registration)/i', $catName)) {
                continue;
            }

            // Check if fee matches this candidate's targeting criteria
            if (!$fee->matchesRegistration($this)) {
                continue;
            }

            $isExtraCat = ($catType === SpmbFeeCategory::TYPE_EXTRA) || in_array($fee->spmb_fee_category_id, $extraCatIds);

            if ($isExtraCat) {
                // Biaya Tambahan: only include if candidate opted for this extra service
                if ($extraServices->isNotEmpty()) {
                    $matches = $extraServices->contains(function($es) use ($feeNameClean) {
                        $esName = strtolower(trim($es->name ?? ''));
                        $esCode = strtolower(trim($es->code ?? ''));
                        return ($feeNameClean === $esName)
                            || ($feeNameClean === $esCode)
                            || (!empty($esCode) && str_contains($feeNameClean, $esCode))
                            || (!empty($feeNameClean) && str_contains($esName, $feeNameClean));
                    });
                    if ($matches) {
                        $selectedMasterFees->push($fee);
                    }
                }
            } else {
                // Regular admission fee: check backward-compatibility grade keyword if targeting was not explicitly set
                $hasExplicitTarget = !empty($fee->applicable_grades) || !empty($fee->applicable_class_programs) || !empty($fee->applicable_types);

                if ($hasExplicitTarget) {
                    $selectedMasterFees->push($fee);
                } else {
                    $feeNameUpper = strtoupper($fee->name);
                    $gradeNameUpper = strtoupper($gradeName);
                    $secGradeNameUpper = strtoupper($this->secondaryGrade->name ?? '');

                    // Fetch all grade names dynamically from master database table
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

                    if (!$hasOtherGradeKeyword) {
                        $selectedMasterFees->push($fee);
                    }
                }
            }
        }

        // 3b. Fallback: For any selected extraService not yet resolved, search across all active extra fee categories
        if ($extraServices->isNotEmpty()) {
            foreach ($extraServices as $es) {
                $esName = strtolower(trim($es->name ?? ''));
                $esCode = strtolower(trim($es->code ?? ''));
                $alreadyFound = $selectedMasterFees->contains(function($f) use ($esName, $esCode) {
                    $fn = strtolower(trim($f->name));
                    return ($fn === $esName || $fn === $esCode || (!empty($esCode) && str_contains($fn, $esCode)) || (!empty($fn) && str_contains($esName, $fn)));
                });
                if (!$alreadyFound) {
                    $fallbackFees = SpmbFee::with('category')
                        ->whereIn('spmb_fee_category_id', $extraCatIds)
                        ->where('is_active', true)
                        ->where(function($q) use ($esName, $esCode) {
                            $q->whereRaw('LOWER(name) = ?', [$esName])
                              ->orWhereRaw('LOWER(name) = ?', [$esCode]);
                            if (!empty($esCode)) {
                                $q->orWhere('name', 'like', '%' . $esCode . '%');
                            }
                        })
                        ->orderByRaw('CASE WHEN spmb_unit_id = ? THEN 0 ELSE 1 END', [$unitId])
                        ->get();

                    $matchingFallback = $fallbackFees->first(function($f) {
                        return $f->matchesRegistration($this);
                    });

                    if ($matchingFallback) {
                        $selectedMasterFees->push($matchingFallback);
                    }
                }
            }
        }

        // 4. Merge master fees with any existing snapshot items
        $snapshotItems = $this->final_fee_snapshot['items'] ?? [];
        $mergedItems = [];
        $processedNames = [];
        $processedCleanNames = [];
        $processedIds = [];

        // First, process active master fees
        foreach ($selectedMasterFees as $f) {
            $nameLower = strtolower(trim($f->name));
            $nameClean = preg_replace('/[^a-z0-9]/', '', $nameLower);
            $processedNames[] = $nameLower;
            $processedCleanNames[] = $nameClean;
            $processedIds[] = (int) $f->id;

            $snapItem = collect($snapshotItems)->first(function($si) use ($f, $nameLower, $nameClean) {
                if (isset($si['id']) && (int)$si['id'] === (int)$f->id) {
                    return true;
                }
                $siNameLower = strtolower(trim($si['name'] ?? ''));
                $siNameClean = preg_replace('/[^a-z0-9]/', '', $siNameLower);
                return ($siNameLower === $nameLower) || ($siNameClean === $nameClean);
            });

            if ($snapItem && !empty($snapItem['name'])) {
                $snapNameLower = strtolower(trim($snapItem['name']));
                $processedNames[] = $snapNameLower;
                $processedCleanNames[] = preg_replace('/[^a-z0-9]/', '', $snapNameLower);
            }

            $paidAmount = $this->getItemPaidAmount($f->name, $f->id);
            $amount = (float) $f->amount;

            // If candidate already paid for this item in history, preserve the snapshot/paid nominal
            if ($paidAmount > 0 && $snapItem && isset($snapItem['amount'])) {
                $amount = max($paidAmount, (float) $snapItem['amount']);
            }

            $gateways = is_array($f->payment_gateway) ? $f->payment_gateway : [$f->payment_gateway];
            if (empty($gateways) || $gateways === ['']) {
                $gateways = ['winpay'];
            }

            $mergedItems[] = [
                'id' => $f->id,
                'name' => $f->name,
                'category_id' => $f->spmb_fee_category_id,
                'category_name' => $f->category->name ?? 'Biaya Administrasi',
                'amount' => $amount,
                'gateways' => $gateways,
                'is_installment_allowed' => $this->isFeeInstallmentAllowed($f->name, $f->id),
            ];
        }

        // Second, if snapshot has legacy items that were already paid, preserve them (excluding registration fees)
        foreach ($snapshotItems as $si) {
            $nameLower = strtolower(trim($si['name'] ?? ''));
            if (preg_match('/(formulir|pendaftaran|registrasi|enrollment|registration)/i', $nameLower)) {
                continue;
            }
            $nameClean = preg_replace('/[^a-z0-9]/', '', $nameLower);
            $siId = isset($si['id']) ? (int)$si['id'] : null;

            if (($siId && in_array($siId, $processedIds)) || in_array($nameLower, $processedNames) || in_array($nameClean, $processedCleanNames)) {
                continue;
            }

            $paidAmount = $this->getItemPaidAmount($si['name'], $siId);
            if ($paidAmount > 0) {
                $mergedItems[] = [
                    'id' => $siId,
                    'name' => $si['name'],
                    'category_id' => null,
                    'category_name' => 'Biaya Administrasi',
                    'amount' => (float) ($si['amount'] ?? $paidAmount),
                    'gateways' => $si['gateways'] ?? ['winpay'],
                    'is_installment_allowed' => false,
                ];
                if ($siId) $processedIds[] = $siId;
                $processedNames[] = $nameLower;
                $processedCleanNames[] = $nameClean;
            }
        }

        $total = array_sum(array_column($mergedItems, 'amount'));

        return [
            'items' => $mergedItems,
            'total' => $total,
        ];
    }

    /**
     * Get complete registration fee details (including multi-item for package like KB/TK + TPA Daycare)
     */
    public function getRegistrationFeeDetails(): array
    {
        $gradeName = strtolower($this->grade->name ?? $this->admission_level ?? '');
        $isTpa1Guru = (str_contains($gradeName, 'guru') || str_contains($gradeName, 'karyawan') || str_contains($gradeName, 'gukar'));

        if ($isTpa1Guru) {
            return [
                'id' => null,
                'items' => [
                    [
                        'id' => null,
                        'name' => 'Biaya Pendaftaran TPA 1 (Khusus Putra/Putri Guru & Karyawan)',
                        'amount' => 0.0,
                        'gateways' => ['winpay']
                    ]
                ],
                'total' => 0.0,
                'name' => 'Biaya Pendaftaran TPA 1 (Khusus Guru & Karyawan)',
                'amount' => 0.0,
                'gateways' => ['winpay'],
                'payment_gateway' => ['winpay'],
                'is_free' => true
            ];
        }

        $regCatIds = SpmbFeeCategory::where('category_type', SpmbFeeCategory::TYPE_REGISTRATION)
            ->orWhere(function($q) {
                $q->where('name', 'like', '%Formulir%')
                  ->orWhere('name', 'like', '%Pendaftaran%')
                  ->orWhere('name', 'like', '%Registrasi%')
                  ->orWhere('name', 'like', '%Enrollment%')
                  ->orWhere('name', 'like', '%Registration%');
            })->pluck('id')->toArray();

        $fees = SpmbFee::where(function($q) {
                $q->where('spmb_unit_id', $this->spmb_unit_id)
                  ->orWhereNull('spmb_unit_id');
            })
            ->where('is_active', true)
            ->where(function($q) use ($regCatIds) {
                if (!empty($regCatIds)) {
                    $q->whereIn('spmb_fee_category_id', $regCatIds);
                }
                $q->orWhere('name', 'like', '%Formulir%')
                  ->orWhere('name', 'like', '%Pendaftaran%')
                  ->orWhere('name', 'like', '%Registrasi%')
                  ->orWhere('name', 'like', '%Enrollment%')
                  ->orWhere('name', 'like', '%Registration%');
            })
            ->get();

        // Fallback: If no active fee found, search fees without is_active filter
        if ($fees->isEmpty()) {
            $fees = SpmbFee::where(function($q) {
                    $q->where('spmb_unit_id', $this->spmb_unit_id)
                      ->orWhereNull('spmb_unit_id');
                })
                ->where(function($q) use ($regCatIds) {
                    if (!empty($regCatIds)) {
                        $q->whereIn('spmb_fee_category_id', $regCatIds);
                    }
                    $q->orWhere('name', 'like', '%Formulir%')
                      ->orWhere('name', 'like', '%Pendaftaran%')
                      ->orWhere('name', 'like', '%Registrasi%')
                      ->orWhere('name', 'like', '%Enrollment%')
                      ->orWhere('name', 'like', '%Registration%');
                })
                ->get();
        }

        // 1. Resolve Base Enrollment Fee
        $isPureTpaGrade = str_contains($gradeName, 'tpa') || str_contains($gradeName, 'daycare') || in_array($this->spmb_grade_id, [13, 15, 16, 17]);

        $baseFees = $fees->filter(function($f) use ($isPureTpaGrade) {
            if ($isPureTpaGrade) {
                return str_contains(strtolower($f->name), 'tpa') || str_contains(strtolower($f->name), 'daycare');
            }
            return !str_contains(strtolower($f->name), 'tpa') && !str_contains(strtolower($f->name), 'tpq') && !str_contains(strtolower($f->name), 'daycare');
        });
        if ($baseFees->isEmpty()) {
            $baseFees = $fees;
        }

        $baseFee = $baseFees->first(function($fee) {
            $hasTarget = !empty($fee->applicable_grades) || !empty($fee->applicable_class_programs) || !empty($fee->applicable_types);
            return $hasTarget && $fee->matchesRegistration($this);
        }) ?? $baseFees->first(function($fee) {
            return $fee->matchesRegistration($this);
        });

        $items = [];
        $total = 0.0;
        $gateways = ['winpay'];

        if ($baseFee) {
            $itemGateways = is_array($baseFee->payment_gateway) ? $baseFee->payment_gateway : [$baseFee->payment_gateway];
            $items[] = [
                'id' => $baseFee->id,
                'name' => $baseFee->name,
                'amount' => (float) $baseFee->amount,
                'gateways' => $itemGateways
            ];
            $total += (float) $baseFee->amount;
            $gateways = $itemGateways;
        } else {
            $fallbackAmt = ($this->unit && !empty($this->unit->registration_fee)) ? (float)$this->unit->registration_fee : 300000.0;
            $fallbackName = ($this->unit && !empty($this->unit->code)) ? ('Enrollment Fee ' . $this->unit->code) : 'Biaya Pendaftaran';
            $items[] = [
                'id' => null,
                'name' => $fallbackName,
                'amount' => $fallbackAmt,
                'gateways' => ['winpay']
            ];
            $total += $fallbackAmt;
        }

        // 2. Check if candidate has TPA Daycare attached (via secondaryGrade, additional_info, or extraServices fallback)
        $hasTpa = ($this->spmb_secondary_grade_id !== null)
            || ($this->secondaryGrade !== null)
            || !empty($this->additional_info['include_tpa'])
            || !empty($this->additional_info['secondary_grade_id'])
            || $this->extraServices->contains(function($es) {
                $n = strtolower($es->name ?? '');
                $c = strtoupper($es->code ?? '');
                return str_contains($n, 'tpa') || str_contains($n, 'penitipan') || str_contains($n, 'daycare') || $c === 'TPA';
            });

        if ($hasTpa && (!isset($baseFee) || !str_contains(strtolower($baseFee->name), 'tpa'))) {
            $targetDaycareGradeId = $this->spmb_secondary_grade_id ?? ($this->additional_info['secondary_grade_id'] ?? null);
            if (!$targetDaycareGradeId) {
                $subUnit = strtolower($this->grade->sub_unit ?? '');
                $gName = strtolower($this->grade->name ?? '');
                if (str_contains($subUnit, 'playgroup') || str_contains($gName, 'kb')) {
                    $targetDaycareGradeId = 16; // TPA 2
                } elseif (str_contains($subUnit, 'tk') || str_contains($gName, 'tk')) {
                    $targetDaycareGradeId = 17; // TPA 3
                }
            }

            $tpaFee = null;
            if ($targetDaycareGradeId) {
                $dummyTpaReg = clone $this;
                $dummyTpaReg->spmb_grade_id = $targetDaycareGradeId;
                $tpaFee = SpmbFee::where('spmb_unit_id', $this->spmb_unit_id)
                    ->where('is_active', true)
                    ->get()
                    ->first(function($fee) use ($dummyTpaReg) {
                        return $fee->matchesRegistration($dummyTpaReg);
                    });
            }

            if (!$tpaFee) {
                $tpaFee = SpmbFee::where('spmb_unit_id', $this->spmb_unit_id)
                    ->where('is_active', true)
                    ->where(function($q) {
                        $q->where('name', 'like', '%Enrollment%TPA%')
                          ->orWhere('name', 'like', '%Pendaftaran%TPA%')
                          ->orWhere('name', 'like', '%Formulir%TPA%')
                          ->orWhere('name', 'like', '%TPA%');
                    })->first() ?: SpmbFee::where('spmb_unit_id', $this->spmb_unit_id)
                    ->where(function($q) {
                        $q->where('name', 'like', '%Enrollment%TPA%')
                          ->orWhere('name', 'like', '%Pendaftaran%TPA%')
                          ->orWhere('name', 'like', '%Formulir%TPA%')
                          ->orWhere('name', 'like', '%TPA%');
                    })->first();
            }

            if ($tpaFee) {
                $tpaGateways = is_array($tpaFee->payment_gateway) ? $tpaFee->payment_gateway : [$tpaFee->payment_gateway];
                $items[] = [
                    'id' => $tpaFee->id,
                    'name' => $tpaFee->name,
                    'amount' => (float) $tpaFee->amount,
                    'gateways' => $tpaGateways
                ];
                $total += (float) $tpaFee->amount;
                $gateways = array_values(array_intersect($gateways, $tpaGateways)) ?: ['winpay'];
            }
        }

        if (count($items) > 1) {
            $subUnitName = $this->grade->sub_unit ?? ($this->unit->code ?? 'PAUD');
            $daycareGradeName = '';
            if ($this->secondaryGrade) {
                $daycareGradeName = $this->secondaryGrade->name;
            } else {
                $gradeNameLower = strtolower($this->grade->name ?? '');
                if (str_contains(strtolower($subUnitName), 'playgroup') || str_contains($gradeNameLower, 'kb')) {
                    $daycareGradeName = 'TPA 2';
                } elseif (str_contains(strtolower($subUnitName), 'tk') || str_contains($gradeNameLower, 'tk')) {
                    $daycareGradeName = 'TPA 3';
                }
            }
            $feeName = $subUnitName . ' + Layanan Daycare' . ($daycareGradeName ? " ({$daycareGradeName})" : '');
        } else {
            $feeName = $items[0]['name'] ?? 'Biaya Pendaftaran';
        }

        return [
            'id' => $baseFee->id ?? null,
            'items' => $items,
            'total' => $total,
            'name' => $feeName,
            'amount' => $total,
            'gateways' => $gateways,
            'payment_gateway' => $gateways,
            'is_free' => ($total <= 0)
        ];
    }

    /**
     * Get the master registration form fee for this candidate
     */
    public function getRegistrationFee()
    {
        $details = $this->getRegistrationFeeDetails();
        return (object) [
            'id' => $details['id'] ?? null,
            'name' => $details['name'] ?? 'Formulir Pendaftaran',
            'amount' => (float) ($details['total'] ?? 300000),
            'payment_gateway' => $details['gateways'] ?? ['winpay'],
            'category' => (object) ['name' => 'Biaya Pendaftaran'],
            'is_active' => true,
        ];
    }

    public function getRegistrationFeeNameAttribute()
    {
        $fee = $this->getRegistrationFee();
        if ($fee) {
            return $fee->name;
        }
        return 'Formulir Pendaftaran ' . ($this->unit->code ?? $this->unit->name ?? '');
    }

    /**
     * Get resolved sub unit display name for this registration
     * Examples: 'Playgroup + Daycare', 'TK + Daycare', 'Playgroup', 'TK', 'Daycare', or null (if SD/SMP)
     */
    public function getSubUnitDisplayNameAttribute(): ?string
    {
        $primarySubUnit = trim($this->grade->sub_unit ?? '');
        if (empty($primarySubUnit) || $primarySubUnit === '-') {
            return null;
        }

        $hasSecondaryDaycare = false;
        if ($this->secondaryGrade) {
            $secSu = strtolower($this->secondaryGrade->sub_unit ?? '');
            $hasSecondaryDaycare = str_contains($secSu, 'daycare') || str_contains($secSu, 'tpa');
        } elseif (!empty($this->spmb_secondary_grade_id)) {
            $hasSecondaryDaycare = true;
        } elseif (!empty($this->additional_info['include_tpa']) || !empty($this->additional_info['secondary_grade_id'])) {
            $hasSecondaryDaycare = true;
        } elseif ($this->relationLoaded('extraServices')) {
            $hasSecondaryDaycare = $this->extraServices->contains(function($es) {
                $n = strtolower($es->name ?? '');
                $c = strtoupper($es->code ?? '');
                return str_contains($n, 'tpa') || str_contains($n, 'penitipan') || str_contains($n, 'daycare') || $c === 'TPA';
            });
        }

        if ($hasSecondaryDaycare && !str_contains(strtolower($primarySubUnit), 'daycare') && !str_contains(strtolower($primarySubUnit), 'tpa')) {
            return $primarySubUnit . ' + Daycare';
        }

        return $primarySubUnit;
    }

    /**
     * Get resolved full grade/class display name for this registration (supporting multi-class like KB A & TPA 2)
     * Examples: 'KB A & TPA 2', 'KB B & TPA 2', 'TK A & TPA 3', 'TK B & TPA 3', 'KB A', 'TK A', 'TPA 2', 'Kelas 1', 'Kelas 7'
     */
    public function getClassDisplayNameAttribute(): string
    {
        $primaryGrade = $this->grade->name ?? ($this->admission_level ?: '-');
        
        if ($this->secondaryGrade && !str_contains(strtolower($primaryGrade), 'tpa') && !str_contains(strtolower($primaryGrade), 'daycare')) {
            return $primaryGrade . ' & ' . $this->secondaryGrade->name;
        }

        $hasSecondaryDaycare = !empty($this->spmb_secondary_grade_id) || !empty($this->additional_info['include_tpa']) || !empty($this->additional_info['secondary_grade_id']);
        if (!$hasSecondaryDaycare && $this->relationLoaded('extraServices')) {
            $hasSecondaryDaycare = $this->extraServices->contains(function($es) {
                $n = strtolower($es->name ?? '');
                $c = strtoupper($es->code ?? '');
                return str_contains($n, 'tpa') || str_contains($n, 'penitipan') || str_contains($n, 'daycare') || $c === 'TPA';
            });
        }

        if ($hasSecondaryDaycare && !str_contains(strtolower($primaryGrade), 'tpa') && !str_contains(strtolower($primaryGrade), 'daycare')) {
            $subUnit = strtolower($this->grade->sub_unit ?? '');
            $gName = strtolower($primaryGrade);
            $daycareGradeName = 'TPA 2';
            if (str_contains($subUnit, 'tk') || str_contains($gName, 'tk')) {
                $daycareGradeName = 'TPA 3';
            }
            return $primaryGrade . ' & ' . $daycareGradeName;
        }

        return $primaryGrade;
    }

    /**
     * Get non-formal extra services (excluding academic Daycare / TPA which is already represented in grade/subunit)
     */
    public function getNonFormalServicesAttribute()
    {
        $services = $this->relationLoaded('extraServices') ? $this->extraServices : $this->extraServices()->get();
        return $services->filter(function($es) {
            $n = strtolower($es->name ?? '');
            $c = strtoupper($es->code ?? '');
            return !str_contains($n, 'tpa') && !str_contains($n, 'daycare') && !str_contains($n, 'penitipan') && $c !== 'TPA';
        });
    }

    /**
     * Calculate total gross fee before discount
     */
    public function getGrossFee()
    {
        $details = $this->getFinalFeeDetails();
        return (float) ($details['total'] ?? 0);
    }

    public function getGrossFeeAttribute()
    {
        return $this->getGrossFee();
    }

    /**
     * Get specific item discount amount for a fee component
     */
    public function getItemDiscountAmount($feeName, $feeId = null)
    {
        $mode = $this->discount_mode ?? 'global';
        if ($mode !== 'selective') {
            return 0;
        }

        $discounts = $this->item_discounts ?? [];
        if (!is_array($discounts)) {
            return 0;
        }

        // 1. Exact name match
        if (isset($discounts[$feeName])) {
            return (float) $discounts[$feeName];
        }

        // 2. ID match if provided
        if ($feeId !== null && isset($discounts[$feeId])) {
            return (float) $discounts[$feeId];
        }

        // 3. Case-insensitive name match
        foreach ($discounts as $key => $amount) {
            if (is_string($key) && strcasecmp(trim($key), trim($feeName)) === 0) {
                return (float) $amount;
            }
        }

        return 0;
    }

    /**
     * Get specific net item amount after selective discount
     */
    public function getItemNetAmount($feeName, $grossAmount, $feeId = null)
    {
        $discount = $this->getItemDiscountAmount($feeName, $feeId);
        return max(0, (float) $grossAmount - $discount);
    }

    /**
     * Calculate total discount amount (Global or sum of Selective discounts)
     */
    public function getTotalDiscountAttribute()
    {
        $mode = $this->discount_mode ?? 'global';
        if ($mode === 'none') {
            return 0;
        }
        if ($mode === 'selective') {
            $discounts = $this->item_discounts ?? [];
            if (is_array($discounts)) {
                return (float) array_sum($discounts);
            }
            return 0;
        }

        return (float) ($this->discount_amount ?? 0);
    }

    /**
     * Net fee after subtracting approved discount
     */
    public function getNetFeeAttribute()
    {
        $gross = $this->getGrossFee();
        $discount = $this->total_discount;
        return max(0, $gross - $discount);
    }

    public function getTotalGrossFinalFeeAttribute()
    {
        return $this->getGrossFee();
    }

    public function getNetFinalFeeAttribute()
    {
        return $this->net_fee;
    }

    public function getRemainingFinalFeeAttribute()
    {
        return $this->remaining_balance;
    }

    /**
     * Total paid final fee amount (principal only, net of gateway admin fee)
     */
    public function getTotalPaidFinalFeeAttribute()
    {
        $successfulPayments = $this->payments()
            ->with('items')
            ->whereIn('status', ['success', 'settled'])
            ->where('payment_type', 'final_fee')
            ->get();

        $totalPrincipal = 0;
        foreach ($successfulPayments as $p) {
            if ($p->items && $p->items->isNotEmpty()) {
                $totalPrincipal += (float) $p->items->sum('amount');
            } else {
                $principal = $p->base_amount ?? ($p->amount - ($p->admin_fee ?? 0));
                $totalPrincipal += (float) $principal;
            }
        }

        return (float) $totalPrincipal;
    }

    /**
     * Remaining unpaid balance
     */
    public function getRemainingBalanceAttribute()
    {
        return max(0, $this->net_fee - $this->total_paid_final_fee);
    }

    /**
     * Check if a specific fee item is allowed to be paid via installment
     */
    public function isFeeInstallmentAllowed($feeName, $feeId = null)
    {
        if ($this->installment_mode === 'all') {
            return true;
        }

        if ($this->installment_mode === 'selective') {
            $allowedIds = $this->installment_allowed_fee_ids ?? [];
            if ($feeId && in_array($feeId, $allowedIds)) {
                return true;
            }
            // Check by name if ID was not provided or stored
            if (is_array($allowedIds)) {
                foreach ($allowedIds as $idOrName) {
                    if (is_string($idOrName) && strcasecmp(trim($idOrName), trim($feeName)) === 0) {
                        return true;
                    }
                }
            }
            // Check against SpmbFee database by name if stored by ID
            if (!empty($allowedIds)) {
                $matchingFee = SpmbFee::where('name', $feeName)
                    ->whereIn('id', $allowedIds)
                    ->first();
                if ($matchingFee) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    /**
     * Calculate total mandatory fees (items not allowed for installment)
     */
    public function getMandatoryFeesTotal()
    {
        if ($this->installment_mode === 'all') {
            return 0;
        }

        $items = $this->final_fee_snapshot['items'] ?? [];
        $mandatoryTotal = 0;

        foreach ($items as $item) {
            $name = $item['name'] ?? '';
            $amount = (float) ($item['amount'] ?? 0);
            $feeId = $item['id'] ?? null;
            $netAmount = $this->getItemNetAmount($name, $amount, $feeId);

            if (!$this->isFeeInstallmentAllowed($name, $feeId)) {
                $mandatoryTotal += $netAmount;
            }
        }

        return $mandatoryTotal;
    }

    /**
     * Calculate minimum payment required for current transaction
     */
    public function getMinimumPaymentRequired()
    {
        $remaining = $this->remaining_balance;
        if ($remaining <= 0) {
            return 0;
        }

        if ($this->installment_mode === 'none' || empty($this->installment_mode)) {
            return $remaining;
        }

        if ($this->installment_mode === 'all') {
            $minInstallment = (float) ($this->min_installment_amount ?: 500000);
            return min($remaining, max(1, $minInstallment));
        }

        if ($this->installment_mode === 'selective') {
            $mandatoryTotal = $this->getMandatoryFeesTotal();
            $mandatoryRemaining = max(0, $mandatoryTotal - $this->total_paid_final_fee);
            $installmentRemaining = max(0, $remaining - $mandatoryRemaining);
            $minInstallment = (float) ($this->min_installment_amount ?: 0);
            
            $installmentPart = min($installmentRemaining, $minInstallment);
            $minRequired = $mandatoryRemaining + $installmentPart;

            return min($remaining, max(1, $minRequired));
        }

        return $remaining;
    }

    /**
     * Get total amount paid specifically for a given fee item (by ID or name)
     */
    public function getItemPaidAmount($itemName, $feeId = null)
    {
        $successfulPayments = $this->payments()
            ->with('items')
            ->whereIn('status', ['success', 'settled'])
            ->where('payment_type', 'final_fee')
            ->get();

        $totalPaid = 0;
        foreach ($successfulPayments as $p) {
            // 1. Check relational payment_items first
            if ($p->items && $p->items->isNotEmpty()) {
                foreach ($p->items as $pItem) {
                    $matchById = ($feeId !== null && $pItem->spmb_fee_id !== null && (int)$pItem->spmb_fee_id === (int)$feeId);
                    $matchByName = (strcasecmp(trim($pItem->fee_name ?? ''), trim($itemName)) === 0);

                    if ($matchById || $matchByName) {
                        $totalPaid += (float) $pItem->amount;
                    }
                }
            } else {
                // 2. Fallback to legacy payment_info['selected_items']
                $info = is_array($p->payment_info) ? $p->payment_info : [];
                $selectedItems = $info['selected_items'] ?? [];
                if (!is_array($selectedItems)) continue;

                $itemCount = count($selectedItems);
                if ($itemCount === 0) continue;

                foreach ($selectedItems as $si) {
                    $siId = $si['id'] ?? null;
                    $matchById = ($feeId !== null && $siId !== null && (int)$siId === (int)$feeId);
                    $matchByName = (strcasecmp(trim($si['name'] ?? ''), trim($itemName)) === 0);

                    if ($matchById || $matchByName) {
                        if ($itemCount === 1) {
                            $principal = (float) ($p->base_amount ?? ($p->amount - ($p->admin_fee ?? 0)));
                            $totalPaid += $principal;
                        } else {
                            $itemAmount = (float) ($si['amount'] ?? 0);
                            $totalSelected = array_sum(array_column($selectedItems, 'amount'));
                            if ($totalSelected > 0) {
                                $principal = (float) ($p->base_amount ?? ($p->amount - ($p->admin_fee ?? 0)));
                                $totalPaid += ($principal * ($itemAmount / $totalSelected));
                            } else {
                                $totalPaid += $itemAmount;
                            }
                        }
                        break;
                    }
                }
            }
        }

        return $totalPaid;
    }
}
