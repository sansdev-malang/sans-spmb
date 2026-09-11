<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbTestimonial extends Model
{
    protected $table = 'spmb_testimonials';

    protected $fillable = [
        'spmb_unit_id',
        'name',
        'role_title',
        'content',
        'rating',
        'avatar_url',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rating' => 'integer',
        'order' => 'integer',
    ];

    public function unit()
    {
        return $this->belongsTo(SpmbUnit::class, 'spmb_unit_id');
    }

    /**
     * Get initials of the person name (e.g. 'Bunda Sarah' => 'BS')
     */
    public function getInitialsAttribute()
    {
        $words = preg_split("/[\s,_-]+/", trim($this->name ?? ''));
        $initials = '';
        foreach ($words as $w) {
            if (!empty($w)) {
                $initials .= mb_substr($w, 0, 1);
            }
            if (mb_strlen($initials) >= 2) {
                break;
            }
        }
        return strtoupper($initials) ?: 'AS';
    }
}
