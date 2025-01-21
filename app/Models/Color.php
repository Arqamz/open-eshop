<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Get the products associated with this color.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
