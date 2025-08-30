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

    public function products()
    {
        return $this->hasMany(Product::class);
    }

}
