<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

     protected $fillable = [
        'user_id',
        'name',
        'address',
        'image',
        'latitude',
        'longitude',
        'status',
    ];

    // كل متجر يخص تاجر
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // كل متجر له فئات
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    // كل متجر له منتجات (لو بدنا مباشرة غير الفئات)
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
