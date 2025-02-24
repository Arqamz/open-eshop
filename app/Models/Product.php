<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'long_description',
        'is_featured',
        'discount',
        'profit_margin',
        'sale_price',
        'cost_price',
        'image1',
        'image2',
        'image3',
        'image4',
        'image5',
        'status',
        'stock',
        'price',
        'weight',
        'category_id',
        'color_id',
        'size_id',
        'slug',
        'seo_keywords',
        'product_group_id',
    ];

    /**
     * Get the category associated with the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the color associated with the product.
     */
    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    /**
     * Get the size associated with the product.
     */
    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    /**
     * Get all variations belonging to the same group.
     */
    public function variations()
    {
        return $this->where('product_group_id', $this->product_group_id)->get();
    }
}
