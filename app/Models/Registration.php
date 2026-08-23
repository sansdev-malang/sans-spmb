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
            'candidate_name', 'nickname', 'nik', 'gender', 'birth_place', 
            'birth_date', 'religion', 'previous_school', 'admission_level', 'father_name', 
            'mother_name', 'parent_phone', 'birth_certificate_path', 'family_card_path'
        ];

        if (in_array($fieldName, $columns)) {
            $val = $this->{$fieldName};
            if ($fieldName === 'birth_date' && $val instanceof \DateTimeInterface) {
                return $val->format('Y-m-d');
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
}
