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
     * Calculate total gross fee before discount
     */
    public function getGrossFee()
    {
        if (!empty($this->final_fee_snapshot) && isset($this->final_fee_snapshot['total'])) {
            return (float) $this->final_fee_snapshot['total'];
        }

        // Fallback: calculate from snapshot items if total not set
        if (!empty($this->final_fee_snapshot['items']) && is_array($this->final_fee_snapshot['items'])) {
            return (float) array_sum(array_column($this->final_fee_snapshot['items'], 'amount'));
        }

        return 0;
    }

    /**
     * Net fee after subtracting approved discount
     */
    public function getNetFeeAttribute()
    {
        $gross = $this->getGrossFee();
        $discount = (float) ($this->discount_amount ?? 0);
        return max(0, $gross - $discount);
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

            if (!$this->isFeeInstallmentAllowed($name, $feeId)) {
                $mandatoryTotal += $amount;
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
}
