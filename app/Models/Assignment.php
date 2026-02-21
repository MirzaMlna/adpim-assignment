<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'code',
        'title',
        'agency',
        'date',
        'time',
        'day_count',
        'location',
        'location_detail',
        'fee_per_day',
        'description'
    ];

    public function attendeds()
    {
        return $this->belongsToMany(Attended::class);
    }
}
