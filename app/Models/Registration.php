<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $guarded = [];

    protected $casts = [
        'birth_date' => 'date',
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

        $columns = [
            'candidate_name', 'nickname', 'nik', 'family_card_no', 'gender', 'birth_place', 
            'birth_date', 'religion', 'previous_school', 'admission_level',
            'address', 'house_number', 'rt', 'rw', 'kelurahan', 'kecamatan', 'city', 'province',
            'father_name', 'father_nik', 'father_address', 'father_phone',
            'mother_name', 'mother_nik', 'mother_address', 'mother_phone',
            'guardian_name', 'guardian_nik', 'guardian_address', 'guardian_phone', 'parent_phone',
            'student_photo_path', 'birth_certificate_path', 'family_card_path', 'diploma_certificate_path',
            'student_card_path', 'special_needs_assessment_path', 'payment_receipt_path',
            'spmb_wave_id', 'spmb_type_id', 'spmb_period_id', 'spmb_class_program_id'
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
     */
    public function getFinalFeeDetails()
    {
        if (!empty($this->final_fee_snapshot) && isset($this->final_fee_snapshot['items']) && is_array($this->final_fee_snapshot['items']) && !empty($this->final_fee_snapshot['items'])) {
            return $this->final_fee_snapshot;
        }

        $unitId = $this->spmb_unit_id;
        $gradeName = $this->grade->name ?? '';
        $extraServices = $this->extraServices ?? collect();

        $regCatIds = SpmbFeeCategory::where(function($q) {
            $q->where('name', 'like', '%Formulir%')
              ->orWhere('name', 'like', '%Pendaftaran%')
              ->orWhere('name', 'like', '%Registrasi%');
        })->pluck('id')->toArray();

        $extraCatIds = SpmbFeeCategory::where(function($q) {
            $q->where('name', 'like', '%Tambahan%')
              ->orWhere('name', 'like', '%Extra%')
              ->orWhere('name', 'like', '%Layanan%');
        })->pluck('id')->toArray();

        $unitFeesQuery = SpmbFee::with('category')
            ->where('spmb_unit_id', $unitId)
            ->where('is_active', true);

        if (!empty($regCatIds)) {
            $unitFeesQuery->whereNotIn('spmb_fee_category_id', $regCatIds);
        }

        $allUnitFees = $unitFeesQuery->get();
        $selectedFees = collect();

        foreach ($allUnitFees as $fee) {
            $isExtraCat = in_array($fee->spmb_fee_category_id, $extraCatIds);

            if ($isExtraCat) {
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
                        $selectedFees->push($fee);
                    }
                }
            } else {
                $feeNameUpper = strtoupper($fee->name);
                $gradeNameUpper = strtoupper($gradeName);
                $gradeKeywords = ['TK A', 'TK B', 'KB', 'PLAY GROUP', 'PLAYGROUP', 'KELAS 1', 'KELAS 7'];
                $hasKeyword = false;
                foreach ($gradeKeywords as $kw) {
                    if (str_contains($feeNameUpper, $kw)) {
                        $hasKeyword = true;
                        if (!empty($gradeNameUpper) && str_contains($feeNameUpper, $gradeNameUpper)) {
                            $selectedFees->push($fee);
                            break;
                        }
                    }
                }
                if (!$hasKeyword) {
                    $selectedFees->push($fee);
                }
            }
        }

        $items = [];
        $total = 0;
        foreach ($selectedFees as $f) {
            $items[] = [
                'id' => $f->id,
                'name' => $f->name,
                'category_id' => $f->spmb_fee_category_id,
                'category_name' => $f->category->name ?? 'Biaya Administrasi',
                'amount' => (float) $f->amount,
            ];
            $total += (float) $f->amount;
        }

        return [
            'items' => $items,
            'total' => $total,
        ];
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
        return (float) $this->payments()
            ->whereIn('status', ['success', 'settled'])
            ->where('payment_type', 'final_fee')
            ->selectRaw('COALESCE(SUM(amount - COALESCE(admin_fee, 0)), 0) as total_principal')
            ->value('total_principal');
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
     * Get total amount paid specifically for a given fee item name
     */
    public function getItemPaidAmount($itemName)
    {
        $successfulPayments = $this->payments()
            ->whereIn('status', ['success', 'settled'])
            ->where('payment_type', 'final_fee')
            ->get();

        $totalPaid = 0;
        foreach ($successfulPayments as $p) {
            $info = is_array($p->payment_info) ? $p->payment_info : [];
            $selectedItems = $info['selected_items'] ?? [];
            if (!is_array($selectedItems)) continue;

            $itemCount = count($selectedItems);
            if ($itemCount === 0) continue;

            foreach ($selectedItems as $si) {
                if (strcasecmp(trim($si['name'] ?? ''), trim($itemName)) === 0) {
                    if ($itemCount === 1) {
                        // Single item payment: all principal belongs to this item
                        $principal = (float) ($p->base_amount ?? ($p->amount - ($p->admin_fee ?? 0)));
                        $totalPaid += $principal;
                    } else {
                        // Multi-item payment
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

        return $totalPaid;
    }
}
