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
        'boarding_date',
        'return_date',
        'transportation',
        'time',
        'day_count',
        'location',
        'location_detail',
        'fee_per_day',
        'description',
        'region_classification'
    ];

    protected $casts = [
        'date' => 'date',
        'boarding_date' => 'date',
        'return_date' => 'date',
    ];

    public function attendeds()
    {
        return $this->belongsToMany(Attended::class);
    }

    public function assignmentUsers()
    {
        return $this->hasMany(AssignmentUser::class);
    }
}
