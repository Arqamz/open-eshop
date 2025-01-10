<?php

namespace App\Models;

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    // Define the fields that are safe for mass assignment
    protected $fillable = ['name', 'email', 'password'];

    // Define hidden attributes, like password and remember_token
    protected $hidden = ['password', 'remember_token'];

    // Define JWT-related methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
