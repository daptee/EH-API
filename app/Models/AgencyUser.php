<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

class AgencyUser extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'agency_users';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'agency_code',
    ];

    protected $hidden = [
        'password',
    ];

    // Hash automático de contraseña
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    // Métodos obligatorios JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}