<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'description', 'price', 'image', 'quantity'
    ];

    // علاقة المنتج بالفئة
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // علاقة المنتج بالمتجر من خلال الفئة
    public function store()
    {
        return $this->hasOneThrough(Store::class, Category::class, 'id', 'id', 'category_id', 'store_id');
    }
}
