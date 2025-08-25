<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
      protected $fillable = ['category_id','name','description','price','image','quantity'];

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function offers() {
        return $this->hasMany(Offer::class);
    }
}
