<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpmbUnit extends Model
{
    protected $guarded = [];

    public function grades()
    {
        return $this->hasMany(SpmbGrade::class);
    }
}
