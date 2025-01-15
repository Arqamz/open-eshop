<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'path'];
    protected $keyType = 'integer'; // Prevent non-integer ID
    public $incrementing = false; // Prevent auto-increment
}
