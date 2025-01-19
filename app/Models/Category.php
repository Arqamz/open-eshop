<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'parent_id', 'active'];

    /**
     * Get the subcategories for this category.
     */
    public function subcategories()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get the parent category for this category.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the products for the category and its subcategories.
     */
    public function products()
    {
        $products = $this->hasMany(Product::class);

        if ($this->subcategories()->exists()) {
            foreach ($this->subcategories as $subcategory) {
            $products = $products->merge($subcategory->products);
            }
        }

        return $products;
    }
}