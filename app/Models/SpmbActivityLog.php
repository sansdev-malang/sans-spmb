<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbActivityLog extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper static method to quickly record activity log
     */
    public static function log(string $action, string $description): void
    {
        self::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name ?? 'System',
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
