<?php

namespace App\Models;

use App\Events\NewStoreEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
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

    protected static function booted(): void
    {
        static::created(function (Store $store) {
            event(new NewStoreEvent($store->load('user')));
        });
    }

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

                $q->leftJoin('distances', function ($join) use ($userCityId) {
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
                    // Sort: Same city first (NULL distance = في منطقتك), then by numeric distance
                    ->orderByRaw('CASE WHEN stores.city_id = ? THEN 0 ELSE CAST(distances.distance AS UNSIGNED) END ASC', [$userCityId]);
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

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Storage::disk('public')->url($value) : null,
        );
    }

    // App/Models/Store.php
    public function reliabilityScore()
    {
        $totalReports = $this->products()->withCount('reports')->get()->sum('reports_count');

        // درجة الموثوقية (مثال):
        $score = 100 - ($totalReports * 5);

        // ضبط الحد الأدنى والأقصى
        if ($score > 100) $score = 100;
        if ($score < 0) $score = 0;

        return $score;
    }

    // App\Models\Store.php
    public function getReliabilityScoreAttribute()
    {
        $totalProducts = $this->products()->count();

        if ($totalProducts == 0) return 5; // لو مافيش منتجات → موثوقية كاملة

        $totalReports = $this->products()->withCount('reports')->get()->sum('reports_count');

        // نحدد الحد الأعلى للبلاغات عشان نوزعها على 5 نجوم
        $maxReports = $totalProducts * 5; // مثلا كل منتج ممكن يكون عنده 5 بلاغات → أقصى عدد بلاغات

        // نحسب النسبة
        $scorePercentage = 1 - min($totalReports / $maxReports, 1); // نسبة "الموثوقية"

        // نحولها لنجوم من 1 إلى 5
        $stars = max(1, round($scorePercentage * 5));

        return $stars;
    }
}
