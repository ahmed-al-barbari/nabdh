<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Product extends Model {
    use HasFactory;

    protected $fillable = [ 'store_id', 'product_id', 'description', 'price', 'quantity', 'image' ];
    protected $appends = [ 'name', 'is_favorite', 'recent_prices' ];

    public function store() {
        return $this->belongsTo( Store::class );
    }

    public function reports() {
        return $this->hasMany( Report::class );
    }

    public function category() {
        return $this->belongsTo( Category::class );
    }

    public function favorites() {
        return $this->hasMany( Favorite::class );
    }

    public function mainProduct(): BelongsTo {
        return $this->belongsTo( MainProduct::class, 'product_id' );
    }

    public function offer(): HasOne {
        return $this->hasOne( Offer::class, 'product_id' );
    }

    public function activeOffer(): HasOne {
        return $this->offer()->where( 'active', true )
        ->whereDate( 'start_date', '<=', now() )
        ->whereDate( 'end_date', '>=', now() );
    }

    public function report(): HasOne {
        return $this->hasOne( Report::class );
    }

    public function getIsFavoriteAttribute() {
        if ( Auth::check() ) {
            return $this->favorites()->where( 'user_id', Auth::id() )->exists();
        }
        return false;
    }

    protected function image(): Attribute {
        return Attribute::make(
            get: fn( $value ) => $value ? Storage::disk( 'public' )->url( $value ) : null,

            set: fn( $value ) => $value instanceof \Illuminate\Http\UploadedFile
            ? $value->store( 'products', 'public' )
            : $value
        );
    }

    protected function isReported(): Attribute {
        return Attribute::make(
            get: fn() => $this->report?->users()->where( 'user_id', Auth::id() )->exists() ?? false,
        );
    }
    protected function name(): Attribute {
        return Attribute::make(
            get: fn() => $this->mainProduct->name,
        );
    }
    protected function recentPrices(): Attribute {
        return Attribute::make(
            get: function () {
                return Product::where( 'product_id', $this->product_id )->get()
                ->pluck( 'price' );
            }
            ,
        );
    }
}
