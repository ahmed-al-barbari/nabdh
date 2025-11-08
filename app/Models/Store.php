<?php

namespace App\Models;

use App\Events\NewStoreEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\BarterResponse;

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

    // Rating removed from appends - calculate on-demand to avoid N+1 queries
    // Use ->withRating() scope or ->getRating() method when needed

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

        $userCityId = Auth::check() ? Auth::user()->city?->id : null;

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

    public function scopeFilterByCategory(Builder $builder, ?object $filter, $category, $mainProductIds)
    {
        if (!$filter) {
            return $builder;
        }

        $userCityId = Auth::check() ? Auth::user()->city?->id : null;

        $builder->when($filter->dependent ?? false, function ($q, $value) use ($mainProductIds, $userCityId) {
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
                    'products' => function ($q2) use ($mainProductIds) {
                        $q2->whereIn('product_id', $mainProductIds);
                    }
                ]);

            } elseif ($value === 'price') {
                $q->join('products', function ($join) use ($mainProductIds) {
                    $join->on('products.store_id', '=', 'stores.id')
                        ->whereIn('products.product_id', $mainProductIds);
                })
                    ->select('stores.*', DB::raw('MIN(products.price) as min_price'))
                    ->groupBy('stores.id')
                    ->orderBy('min_price', 'asc');
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

    /**
     * Scope to append rating (only when needed to avoid N+1)
     */
    public function withRating()
    {
        return $this->append('rating');
    }

    /**
     * Calculate store rating - optimized method to avoid N+1 queries
     * Call this method instead of accessing ->rating attribute directly
     */
    public function getRating(): float
    {
        // Ensure relationships are loaded
        if (!$this->relationLoaded('user')) {
            $this->load('user');
        }
        if (!$this->relationLoaded('products')) {
            $this->load('products.reports');
        }

        return $this->calculateRating();
    }

    /**
     * Optimized rating calculation using pre-loaded trade statistics
     * This avoids additional queries when batch processing stores
     */
    public function getRatingOptimized($tradeStats = null): float
    {
        // Ensure relationships are loaded
        if (!$this->relationLoaded('user')) {
            $this->load('user');
        }
        if (!$this->relationLoaded('products')) {
            $this->load('products.reports');
        }

        return $this->calculateRatingOptimized($tradeStats);
    }

    /**
     * Calculate store rating based on price score, reliability, and trade completion
     * Returns a value between 1.0 and 5.0
     * Optimized to use loaded relationships
     */
    protected function rating(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->calculateRating();
            }
        );
    }

    /**
     * Internal rating calculation - optimized
     */
    private function calculateRating(): float
    {
        // 3. Trade completion rate (30% weight) - single optimized query
        $tradeStats = BarterResponse::where('user_id', $this->user_id)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
            ->first();
        
        return $this->calculateRatingWithTradeStats($tradeStats);
    }

    /**
     * Optimized rating calculation using pre-loaded trade stats
     */
    private function calculateRatingOptimized($preloadedTradeStats = null): float
    {
        $tradeStats = null;
        if ($preloadedTradeStats && isset($preloadedTradeStats[$this->user_id])) {
            $tradeStats = $preloadedTradeStats[$this->user_id];
        } else {
            // Fallback to query if not preloaded
            $tradeStats = BarterResponse::where('user_id', $this->user_id)
                ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
                ->first();
        }
        
        return $this->calculateRatingWithTradeStats($tradeStats);
    }

    /**
     * Shared rating calculation logic
     */
    private function calculateRatingWithTradeStats($tradeStats): float
    {
        // 1. Price rating score (40% weight)
        $priceScore = $this->price_rating_score ?? 2;
        $priceNormalized = 1 + ($priceScore / 3) * 4;

        // 2. Reliability score (30% weight) - optimized
        $reliabilityScore = $this->user ? $this->calculateUserReliabilityScoreOptimized($tradeStats) : 50;
        $reliabilityNormalized = 1 + ($reliabilityScore / 100) * 4;

        // 3. Trade completion rate (30% weight)
        $totalTrades = $tradeStats->total ?? 0;
        $completedTrades = $tradeStats->completed ?? 0;
        $tradeRate = $totalTrades > 0 ? ($completedTrades / $totalTrades) : 0.8;
        $tradeNormalized = 1 + $tradeRate * 4;

        // Weighted average
        $rating = ($priceNormalized * 0.4) + ($reliabilityNormalized * 0.3) + ($tradeNormalized * 0.3);

        return round(max(1.0, min(5.0, $rating)), 1);
    }

    /**
     * Calculate user reliability score - optimized to use loaded relationships
     */
    private function calculateUserReliabilityScore(): float
    {
        $tradeStats = BarterResponse::where('user_id', $this->user_id)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
            ->first();
        
        return $this->calculateUserReliabilityScoreOptimized($tradeStats);
    }

    /**
     * Optimized reliability calculation using pre-loaded trade stats
     */
    private function calculateUserReliabilityScoreOptimized($tradeStats = null): float
    {
        $user = $this->user;
        if (!$user) {
            return 50;
        }

        $score = 0;

        // 1. Phone verification (30%) - Verified identity is crucial baseline
        $score += $user->phone ? 30 : 0;

        // 2. Completed trades (35%) - Transaction reliability is key trust indicator
        $totalTrades = $tradeStats->total ?? 0;
        $completedTrades = $tradeStats->completed ?? 0;
        // Give partial credit for having trades even if not all completed
        if ($totalTrades > 0) {
            $completionRate = $completedTrades / $totalTrades;
            $tradeScore = $completionRate * 35;
        } else {
            // New users: give small base score to avoid harsh penalty
            $tradeScore = 5; // 5% base score for new users
        }
        $score += $tradeScore;

        // 3. Reports (35%) - Product quality shows merchant reliability
        $productScores = [];
        foreach ($this->products ?? [] as $product) {
            // Ensure reports are loaded
            if (!$product->relationLoaded('reports')) {
                $product->load('reports');
            }
            
            $totalReports = $product->reports->count();
            $reviewedReports = $product->reports->where('status', 'reviewed')->count();
            $productScore = $totalReports > 0 ? ($reviewedReports / $totalReports) : 1;
            $productScores[] = $productScore;
        }

        if (count($productScores) > 0) {
            $averageProductScore = array_sum($productScores) / count($productScores);
            $score += $averageProductScore * 35;
        } else {
            // Users without products get base score
            $score += 20; // 20% base score for non-merchants
        }

        return min(100, max(0, $score));
    }

}
