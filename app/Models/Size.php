<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Get the products associated with this size.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}