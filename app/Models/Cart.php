<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['customer_id'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function deleteCart()
    {
        $this->cartItems()->delete();
        $this->delete();
    }
}
