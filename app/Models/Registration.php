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
    ];

    protected $appends = [
        'registration_fee_name',
    ];

    public function scopeScopedByAdmin($query)
    {
        if (auth()->check() && auth()->user()->spmb_unit_id) {
            return $query->where('spmb_unit_id', auth()->user()->spmb_unit_id);
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
            'father_name', 'father_nik', 'father_address', 'father_phone',
            'mother_name', 'mother_nik', 'mother_address', 'mother_phone',
            'guardian_name', 'guardian_nik', 'guardian_address', 'guardian_phone', 'parent_phone',
            'student_photo_path', 'birth_certificate_path', 'family_card_path', 'diploma_certificate_path',
            'student_card_path', 'special_needs_assessment_path', 'payment_receipt_path',
            'spmb_wave_id', 'spmb_type_id', 'spmb_period_id', 'spmb_class_program_id',
            'observation_date', 'observation_time', 'observation_location', 'observation_interviewer', 'observation_notes'
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
        $regCatIds = SpmbFeeCategory::where(function($q) {
            $q->where('name', 'like', '%Formulir%')
              ->orWhere('name', 'like', '%Pendaftaran%')
              ->orWhere('name', 'like', '%Registrasi%');
        })->pluck('id')->toArray();

        // 2. Identify Extra Services / Biaya Tambahan Category IDs
        $extraCatIds = SpmbFeeCategory::where(function($q) {
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
            $isExtraCat = in_array($fee->spmb_fee_category_id, $extraCatIds);

            if ($isExtraCat) {
                // Biaya Tambahan: only include if candidate opted for this extra service
                if ($extraServices->isNotEmpty()) {
                    $feeNameClean = strtolower(trim($fee->name));
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
                // Regular admission fee: check if fee is explicitly targeted to another grade
                $feeNameUpper = strtoupper($fee->name);
                $gradeNameUpper = strtoupper($gradeName);

                $allGradeKeywords = ['TK A', 'TK B', 'KB', 'PLAY GROUP', 'PLAYGROUP', 'KELAS 1', 'KELAS 2', 'KELAS 3', 'KELAS 4', 'KELAS 5', 'KELAS 6', 'KELAS 7', 'KELAS 8', 'KELAS 9'];
                $hasOtherGradeKeyword = false;

                foreach ($allGradeKeywords as $kw) {
                    if (str_contains($feeNameUpper, $kw)) {
                        if (!empty($gradeNameUpper) && (
                            str_contains($gradeNameUpper, $kw) ||
                            ($kw === 'PLAY GROUP' && str_contains($gradeNameUpper, 'KB')) ||
                            ($kw === 'KB' && str_contains($gradeNameUpper, 'PLAY GROUP'))
                        )) {
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
                    $fallbackFee = SpmbFee::with('category')
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
                        ->first();
                    if ($fallbackFee) {
                        $selectedMasterFees->push($fallbackFee);
                    }
                }
            }
        }

        // 4. Merge master fees with any existing snapshot items
        $snapshotItems = $this->final_fee_snapshot['items'] ?? [];
        $mergedItems = [];
        $processedNames = [];

        // First, process active master fees
        foreach ($selectedMasterFees as $f) {
            $nameLower = strtolower(trim($f->name));
            $processedNames[] = $nameLower;

            $snapItem = collect($snapshotItems)->first(fn($si) => strtolower(trim($si['name'] ?? '')) === $nameLower);
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

        // Second, if snapshot has legacy items that were already paid, preserve them
        foreach ($snapshotItems as $si) {
            $nameLower = strtolower(trim($si['name'] ?? ''));
            if (!in_array($nameLower, $processedNames)) {
                $paidAmount = $this->getItemPaidAmount($si['name'], $si['id'] ?? null);
                if ($paidAmount > 0) {
                    $mergedItems[] = [
                        'id' => $si['id'] ?? null,
                        'name' => $si['name'],
                        'category_id' => null,
                        'category_name' => 'Biaya Administrasi',
                        'amount' => (float) ($si['amount'] ?? $paidAmount),
                        'gateways' => $si['gateways'] ?? ['winpay'],
                        'is_installment_allowed' => false,
                    ];
                }
            }
        }

        $total = array_sum(array_column($mergedItems, 'amount'));

        return [
            'items' => $mergedItems,
            'total' => $total,
        ];
    }

    /**
     * Get the master registration form fee for this candidate
     */
    public function getRegistrationFee()
    {
        $regCat = SpmbFeeCategory::where(function($q) {
            $q->where('name', 'like', '%Formulir%')
              ->orWhere('name', 'like', '%Pendaftaran%')
              ->orWhere('name', 'like', '%Registrasi%');
        })->first();

        if ($regCat) {
            $fee = SpmbFee::where('spmb_fee_category_id', $regCat->id)
                ->where('spmb_unit_id', $this->spmb_unit_id)
                ->where('is_active', true)
                ->first();
            if ($fee) {
                return $fee;
            }
        }

        return SpmbFee::where('spmb_unit_id', $this->spmb_unit_id)
            ->where('is_active', true)
            ->whereHas('category', function($q) {
                $q->where('name', 'like', '%Formulir%')
                  ->orWhere('name', 'like', '%Pendaftaran%');
            })->first();
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
