<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'attended_id',
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

    public function attended()
    {
        return $this->belongsTo(Attended::class);
    }

    public function getTotalFeeAttribute()
    {
        return $this->day_count * $this->fee_per_day;
    }
}
