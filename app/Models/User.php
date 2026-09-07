<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'spmb_unit_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isUnitAdmin(): bool
    {
        return $this->role === 'admin' && !empty($this->spmb_unit_id);
    }

    /**
     * Get relevant admins for a specific unit (Super Admins + Unit Admin of that unit)
     */
    public static function getAdminsForUnit($unitId = null)
    {
        return static::where('role', 'super_admin')
            ->orWhere(function($q) use ($unitId) {
                $q->where('role', 'admin');
                if ($unitId) {
                    $q->where('spmb_unit_id', $unitId);
                }
            })->get();
    }

    public function spmbUnit()
    {
        return $this->belongsTo(SpmbUnit::class, 'spmb_unit_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
