<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assignment_id',
        'departure_location',
        'destination_location',
        'video_report',
        'photo_report',
        'social_media_report',
        'news_report',
        'duty_proof',
        'is_verified',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }
}
