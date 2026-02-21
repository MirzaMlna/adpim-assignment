<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attended extends Model
{
    protected $fillable = [
        'name',
        'rank',
        'rank_abbreviation'
    ];

    public function assignments()
    {
        return $this->belongsToMany(Assignment::class);
    }
}
