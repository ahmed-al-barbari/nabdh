<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MainProduct extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'category_id',
    ];

    public function category() {
        return $this->belongsTo( Category::class );
    }

    public function products(): HasMany {
        return $this->hasMany( Product::class, 'product_id' );
    }
}
