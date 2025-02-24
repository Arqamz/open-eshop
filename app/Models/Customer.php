<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPassword;

class Customer extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use CanResetPassword;

    protected $fillable = [
        'first_name', 
        'last_name', 
        'email', 
        'password', 
        'phone_number', 
        'address_line1', 
        'address_line2', 
        'city', 
        'state', 
        'zip_code', 
        'country'
    ];

    protected $hidden = ['password', 'remember_token'];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    protected static function booted()
    {
        static::created(function ($customer) {
            $customer->cart()->create();
        });
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => 'customer',
            'guard' => 'customer',
        ];
    }
}
