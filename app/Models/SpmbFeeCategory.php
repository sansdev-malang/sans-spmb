<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbFeeCategory extends Model
{
    const TYPE_REGISTRATION = 'registration_fee';
    const TYPE_TUITION = 'tuition_fee';
    const TYPE_EXTRA = 'extra_service';

    protected $guarded = [];

    public function fees()
    {
        return $this->hasMany(SpmbFee::class, 'spmb_fee_category_id');
    }

    public function units()
    {
        return $this->belongsToMany(SpmbUnit::class, 'spmb_fee_category_unit', 'spmb_fee_category_id', 'spmb_unit_id');
    }

    public function isRegistration(): bool
    {
        return $this->category_type === self::TYPE_REGISTRATION;
    }

    public function isTuition(): bool
    {
        return $this->category_type === self::TYPE_TUITION;
    }

    public function isExtraService(): bool
    {
        return $this->category_type === self::TYPE_EXTRA;
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->category_type) {
            self::TYPE_REGISTRATION => 'Biaya Pendaftaran Awal',
            self::TYPE_EXTRA => 'Layanan Tambahan',
            default => 'Biaya Masuk / Administrasi',
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match($this->category_type) {
            self::TYPE_REGISTRATION => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800',
            self::TYPE_EXTRA => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800',
            default => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
        };
    }

    public function scopeRegistration($query)
    {
        return $query->where('category_type', self::TYPE_REGISTRATION);
    }

    public function scopeTuition($query)
    {
        return $query->where('category_type', self::TYPE_TUITION);
    }

    public function scopeExtraService($query)
    {
        return $query->where('category_type', self::TYPE_EXTRA);
    }

    /**
     * Get dynamic category name configured in database for initial registration fee
     */
    public static function getRegistrationCategoryName(): string
    {
        return static::where('category_type', self::TYPE_REGISTRATION)->value('name')
            ?? static::where('name', 'like', '%Enrollment%')->orWhere('name', 'like', '%Formulir%')->value('name')
            ?? 'Enrollment Fee';
    }

    /**
     * Get dynamic category name configured in database for admission / tuition fee
     */
    public static function getTuitionCategoryName(): string
    {
        return static::where('category_type', self::TYPE_TUITION)->value('name')
            ?? static::where('name', 'like', '%Administrasi%')->orWhere('name', 'like', '%DSP%')->value('name')
            ?? 'Biaya Masuk & DSP';
    }

    /**
     * Get dynamic category name configured in database for extra services
     */
    public static function getExtraCategoryName(): string
    {
        return static::where('category_type', self::TYPE_EXTRA)->value('name')
            ?? 'Layanan Tambahan';
    }
}
