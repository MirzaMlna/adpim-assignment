<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'sub_division_id',
        'code',
        'email',
        'password',
        'nip',
        'name',
        'rank',
        'job_title',
        'role',
        'is_active',
        'note',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $lastId = self::max('id') + 1;
            $user->code = 'ADP-' . str_pad($lastId, 3, '0', STR_PAD_LEFT);
        });
    }

    public function subDivision()
    {
        return $this->belongsTo(SubDivision::class);
    }

    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }
}
