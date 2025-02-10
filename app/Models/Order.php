<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_zip',
        'shipping_country',
        'total_price',
        'status',
    ];

    /**
     * Get the customer who placed the order.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the address for this order.
     */
    public function address()
    {
        return $this->has(Address::class);
    }

    /**
     * Get the order items for this order.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
