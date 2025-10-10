<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;


    protected $fillable = ['store_id', 'product_id', 'description', 'price', 'quantity', 'image'];
    protected $appends = ['name'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function reports()
{
    return $this->hasMany(Report::class);
}

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }


    public function mainProduct(): BelongsTo
    {
        return $this->belongsTo(MainProduct::class, 'product_id');
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Storage::disk('public')->url($value) : null,

            set: fn($value) => $value instanceof \Illuminate\Http\UploadedFile
            ? $value->store('products', 'public')
            : $value
        );
    }
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->mainProduct->name,
        );
    }
}
