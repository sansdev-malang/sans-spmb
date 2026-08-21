<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $guarded = [];

    protected $casts = [
        'birth_date' => 'date',
        'additional_info' => 'array',
    ];

    public function getFieldValue($fieldName)
    {
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
}
