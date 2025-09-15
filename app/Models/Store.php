<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Expr\Cast\Object_;
use Storage;

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
        'city_id',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function scopeFilter(Builder $builder, ?object $filter, MainProduct $product)
    {
        if (!$filter) {
            return $builder;
        }

        $userCityId = Auth::user()->city?->id;

        $builder->when($filter->dependent ?? false, function ($q, $value) use ($product, $userCityId) {
            $value = strtolower($value);

            if ($value === 'distance') {
                if (!$userCityId) {
                    return;
                }

                $q->join('distances', function ($join) use ($userCityId) {
                    $join->on(function ($query) use ($userCityId) {
                        $query->whereColumn('distances.city_id_one', 'stores.city_id')
                            ->where('distances.city_id_two', $userCityId)
                            ->orWhere(function ($q2) use ($userCityId) {
                                $q2->whereColumn('distances.city_id_two', 'stores.city_id')
                                    ->where('distances.city_id_one', $userCityId);
                            });
                    });
                })
                    ->select('stores.*', 'distances.distance')
                    ->orderBy('distances.distance', 'asc');
            } elseif ($value === 'rating') {
                $q->with([
                    'products' => function ($q2) use ($product) {
                        $q2->where('product_id', $product->id);
                    }
                ]);

            } elseif ($value === 'price') {
                $q->join('products', function ($join) use ($product) {
                    $join->on('products.store_id', '=', 'stores.id')
                        ->where('products.product_id', $product->id);
                })
                    ->orderBy('products.price', 'asc')
                    ->select('stores.*');
            }
        });

        return $builder;
    }

    // public function percentile(array $sorted, $percentile)
    // {
    //     $index = ($percentile / 100) * (count($sorted) - 1);
    //     $lower = floor($index);
    //     $upper = ceil($index);
    //     $weight = $index - $lower;
    //     if ($upper >= count($sorted))
    //         return $sorted[$lower];
    //     return $sorted[$lower] * (1 - $weight) + $sorted[$upper] * $weight;
    // }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Storage::disk('public')->url($value) : null,
        );
    }

    public function percentile(array $sorted, $percentile)
    {
        $index = ($percentile / 100) * (count($sorted) - 1);
        $lower = floor($index);
        $upper = ceil($index);
        $weight = $index - $lower;
        if ($upper >= count($sorted))
            return $sorted[$lower];
        return $sorted[$lower] * (1 - $weight) + $sorted[$upper] * $weight;
    }


}
