<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\MainProduct;
use App\Models\Store;
use App\Models\Category;
use App\Enums\ApiMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchController extends Controller
{
    public function searchStores(Request $request, MainProduct $product)
    {
        $perPage = $request->per_page ?? 10;
        $page = $request->input('page', 1);
        $filter = json_decode($request->filter);

        // get stores that have the specified product
        $stores = Store::whereRelation('products', function ($q) use ($product) {
            return $q->where('product_id', $product->id);
        })->with([
                'city',
                'user',
                'products' => function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                },
                'products.reports'
            ])
            ->filter($filter, $product)
            ->get();
        
        // Batch load trade statistics for all users to avoid N+1 queries
        $userIds = $stores->pluck('user_id')->unique()->filter()->values();
        $tradeStats = collect();
        
        if ($userIds->isNotEmpty()) {
            $tradeStats = \App\Models\BarterResponse::whereIn('user_id', $userIds)
                ->selectRaw('user_id, COUNT(*) as total, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
                ->groupBy('user_id')
                ->get()
                ->keyBy('user_id');
        }
        
        // Calculate ratings efficiently using pre-loaded trade stats
        $stores->each(function ($store) use ($tradeStats) {
            $store->rating = $store->getRatingOptimized($tradeStats);
        });

        // apply price rating if rating filter is selected
        if (!empty($filter) && strtolower($filter->dependent ?? '') === 'rating') {
            $stores = $this->applyPriceRating($stores, $product);
        }

        // paginate results
        $paginatedStores = new LengthAwarePaginator(
            $stores->forPage($page, $perPage),
            $stores->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json([
            'message' => ApiMessage::STORES_FETCHED->value,
            'stores' => $paginatedStores,
        ]);
    }

    public function searchStoresByCategory(Request $request, Category $category)
    {
        $perPage = $request->per_page ?? 10;
        $page = $request->input('page', 1);
        $filter = json_decode($request->filter);

        // Get all main products in this category
        $mainProductIds = MainProduct::where('category_id', $category->id)->pluck('id');

        if ($mainProductIds->isEmpty()) {
            // No products in this category
            $paginatedStores = new LengthAwarePaginator(
                collect(),
                0,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return response()->json([
                'message' => ApiMessage::STORES_FETCHED->value,
                'stores' => $paginatedStores,
            ]);
        }

        // Get stores that have products in this category
        $stores = Store::whereHas('products', function ($q) use ($mainProductIds) {
            $q->whereIn('product_id', $mainProductIds);
        })->with([
                'city',
                'user',
                'products' => function ($q) use ($mainProductIds) {
                    $q->whereIn('product_id', $mainProductIds);
                },
                'products.reports',
                'products.mainProduct'
            ])
            ->filterByCategory($filter, $category, $mainProductIds)
            ->get();
        
        // Batch load trade statistics for all users to avoid N+1 queries
        $userIds = $stores->pluck('user_id')->unique()->filter()->values();
        $tradeStats = collect();
        
        if ($userIds->isNotEmpty()) {
            $tradeStats = \App\Models\BarterResponse::whereIn('user_id', $userIds)
                ->selectRaw('user_id, COUNT(*) as total, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
                ->groupBy('user_id')
                ->get()
                ->keyBy('user_id');
        }
        
        // Calculate ratings efficiently using pre-loaded trade stats
        $stores->each(function ($store) use ($tradeStats) {
            $store->rating = $store->getRatingOptimized($tradeStats);
        });

        // Apply price rating if rating filter is selected
        if (!empty($filter) && strtolower($filter->dependent ?? '') === 'rating') {
            $stores = $this->applyPriceRatingForCategory($stores, $category, $mainProductIds);
        }

        // Paginate results
        $paginatedStores = new LengthAwarePaginator(
            $stores->forPage($page, $perPage),
            $stores->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json([
            'message' => ApiMessage::STORES_FETCHED->value,
            'stores' => $paginatedStores,
        ]);
    }

    /**
     * Apply price rating to stores for category search
     * Uses the minimum price of products in the category for each store
     */
    private function applyPriceRatingForCategory($stores, $category, $mainProductIds)
    {
        // Collect all prices for products in this category across all stores
        $allPrices = collect();
        $storesWithPrices = $stores->filter(function ($store) use (&$allPrices, $mainProductIds) {
            // Get the minimum price product in this category for this store
            $categoryProducts = $store->products->filter(function ($product) use ($mainProductIds) {
                return in_array($product->product_id, $mainProductIds->toArray());
            });
            
            if ($categoryProducts->isNotEmpty()) {
                $minPrice = $categoryProducts->min('price');
                if ($minPrice && $minPrice > 0) {
                    $allPrices->push($minPrice);
                    return true;
                }
            }
            return false;
        });

        if ($storesWithPrices->isEmpty()) {
            return $stores->map(function ($store) {
                $store->price_rating = 'no_rating';
                $store->price_rating_score = 0;
                return $store;
            });
        }

        // Convert to array and sort numerically
        $pricesArray = $allPrices->sortBy(function($price) {
            return $price;
        })->values()->all();

        // Calculate market statistics
        $marketStats = $this->calculateMarketStatistics($pricesArray, null);

        // Rate each store using the calculated statistics
        return $storesWithPrices->map(function ($store) use ($marketStats, $mainProductIds) {
            $categoryProducts = $store->products->filter(function ($product) use ($mainProductIds) {
                return in_array($product->product_id, $mainProductIds->toArray());
            });
            $storePrice = $categoryProducts->min('price');

            // Determine rating based on sample size and data quality
            if ($marketStats['strategy'] === 'small_sample') {
                $rating = $this->rateSmallSample($storePrice, $marketStats);
            } elseif ($marketStats['strategy'] === 'high_variance') {
                $rating = $this->rateHighVariance($storePrice, $marketStats);
            } elseif ($marketStats['strategy'] === 'low_variance') {
                $rating = $this->rateLowVariance($storePrice, $marketStats);
            } else {
                $rating = $this->rateStandard($storePrice, $marketStats);
            }

            $store->price_rating = $rating['rating'];
            $store->price_rating_score = $rating['score'];

            return $store;
        })->merge($stores->reject(function ($store) use ($mainProductIds) {
            $categoryProducts = $store->products->filter(function ($product) use ($mainProductIds) {
                return in_array($product->product_id, $mainProductIds->toArray());
            });
            return $categoryProducts->isNotEmpty() && $categoryProducts->min('price') > 0;
        })->map(function ($store) {
            $store->price_rating = 'no_rating';
            $store->price_rating_score = 0;
            return $store;
        }))->sortByDesc('price_rating_score')->values();
    }

    /**
     * Apply price rating to stores based on sophisticated statistical analysis
     * Handles all edge cases: small samples, outliers, low/high variance, etc.
     */
    private function applyPriceRating($stores, $product)
    {
        // First, collect all prices for this product across all stores
        $allPrices = collect();
        $storesWithPrices = $stores->filter(function ($store) use (&$allPrices) {
            $productItem = $store->products->first();
            if ($productItem && $productItem->price > 0) {
                $allPrices->push($productItem->price);
                return true;
            }
            return false;
        });

        if ($storesWithPrices->isEmpty()) {
            return $stores->map(function ($store) {
                $store->price_rating = 'no_rating';
                $store->price_rating_score = 0;
                return $store;
            });
        }

        // Get base price as fallback
        $basePrice = $product->price ?? null;

        // Convert to array and sort numerically
        $pricesArray = $allPrices->sortBy(function($price) {
            return $price;
        })->values()->all();

        // Calculate market statistics once for all stores
        $marketStats = $this->calculateMarketStatistics($pricesArray, $basePrice);

        // Rate each store using the calculated statistics
        return $storesWithPrices->map(function ($store) use ($marketStats) {
            $productItem = $store->products->first();
            $storePrice = $productItem->price;

            // Determine rating based on sample size and data quality
            if ($marketStats['strategy'] === 'small_sample') {
                $rating = $this->rateSmallSample($storePrice, $marketStats);
            } elseif ($marketStats['strategy'] === 'high_variance') {
                $rating = $this->rateHighVariance($storePrice, $marketStats);
            } elseif ($marketStats['strategy'] === 'low_variance') {
                $rating = $this->rateLowVariance($storePrice, $marketStats);
            } else {
                $rating = $this->rateStandard($storePrice, $marketStats);
            }

            $store->price_rating = $rating['rating'];
            $store->price_rating_score = $rating['score'];

            return $store;
        })->merge($stores->reject(function ($store) {
            return $store->products->first() && $store->products->first()->price > 0;
        })->map(function ($store) {
            $store->price_rating = 'no_rating';
            $store->price_rating_score = 0;
            return $store;
        }))->sortByDesc('price_rating_score')->values();
    }

    /**
     * Calculate comprehensive market statistics
     */
    private function calculateMarketStatistics(array $sortedPrices, ?float $basePrice): array
    {
        $count = count($sortedPrices);
        $min = $sortedPrices[0];
        $max = $sortedPrices[$count - 1];
        $median = $this->percentile($sortedPrices, 50);
        $mean = array_sum($sortedPrices) / $count;

        // Calculate variance and standard deviation
        $variance = 0;
        foreach ($sortedPrices as $price) {
            $variance += pow($price - $mean, 2);
        }
        $variance = $variance / $count;
        $stdDev = sqrt($variance);

        // Coefficient of Variation (CV) - measures relative variability
        $coefficientOfVariation = $mean > 0 ? ($stdDev / $mean) : 0;

        // Calculate quartiles
        $q1 = $this->percentile($sortedPrices, 25);
        $q3 = $this->percentile($sortedPrices, 75);
        $iqr = $q3 - $q1;

        // Detect outliers using IQR method (if IQR > 0)
        $outliers = [];
        if ($iqr > 0 && $count >= 5) {
            $lowerFence = $q1 - 1.5 * $iqr;
            $upperFence = $q3 + 1.5 * $iqr;
            foreach ($sortedPrices as $price) {
                if ($price < $lowerFence || $price > $upperFence) {
                    $outliers[] = $price;
                }
            }
        }

        // Determine strategy based on data characteristics
        $strategy = 'standard';
        
        if ($count < 5) {
            $strategy = 'small_sample';
        } elseif ($iqr == 0 || $coefficientOfVariation < 0.05) {
            // Very low variance - prices are almost identical
            $strategy = 'low_variance';
        } elseif ($coefficientOfVariation > 0.30 || count($outliers) > $count * 0.3) {
            // High variance - prices are very spread out
            $strategy = 'high_variance';
        }

        // Calculate trimmed mean (remove top/bottom 10% if enough data)
        $trimmedMean = $mean;
        if ($count >= 10) {
            $trimCount = max(1, (int)floor($count * 0.1));
            $trimmedPrices = array_slice($sortedPrices, $trimCount, $count - 2 * $trimCount);
            $trimmedMean = array_sum($trimmedPrices) / count($trimmedPrices);
        }

        return [
            'count' => $count,
            'min' => $min,
            'max' => $max,
            'mean' => $mean,
            'median' => $median,
            'trimmed_mean' => $trimmedMean,
            'std_dev' => $stdDev,
            'variance' => $variance,
            'coefficient_of_variation' => $coefficientOfVariation,
            'q1' => $q1,
            'q3' => $q3,
            'iqr' => $iqr,
            'outliers' => $outliers,
            'outlier_count' => count($outliers),
            'strategy' => $strategy,
            'base_price' => $basePrice,
            'all_prices' => $sortedPrices, // Needed for percentile rank calculation
        ];
    }

    /**
     * Rate price for small sample sizes (2-4 stores)
     */
    private function rateSmallSample(float $price, array $stats): array
    {
        $median = $stats['median'];
        $mean = $stats['mean'];
        $basePrice = $stats['base_price'];
        
        // Use median as reference (more robust than mean for small samples)
        $reference = $median;
        
        // If base price exists and is reasonable, use weighted average
        if ($basePrice && $basePrice > 0) {
            // Weight: 60% market median, 40% base price
            $reference = ($median * 0.6) + ($basePrice * 0.4);
        }

        $deviation = abs($price - $reference) / $reference;

        if ($price <= $reference) {
            // At or below reference
            if ($deviation <= 0.05) { // Within 5%
                return ['rating' => 'best', 'score' => 3];
            } else {
                return ['rating' => 'good', 'score' => 2];
            }
        } else {
            // Above reference
            if ($deviation <= 0.15) { // Within 15%
                return ['rating' => 'good', 'score' => 2];
            } else {
                return ['rating' => 'high', 'score' => 1];
            }
        }
    }

    /**
     * Rate price for standard market conditions (5+ stores, normal variance)
     */
    private function rateStandard(float $price, array $stats): array
    {
        $median = $stats['median'];
        $q1 = $stats['q1'];
        $q3 = $stats['q3'];
        $iqr = $stats['iqr'];
        
        // Adaptive IQR multiplier based on variance
        $cv = $stats['coefficient_of_variation'];
        $iqrMultiplier = 1.0;
        
        // Adjust multiplier based on coefficient of variation
        if ($cv > 0.15) {
            $iqrMultiplier = 1.5; // More lenient for higher variance
        } elseif ($cv < 0.08) {
            $iqrMultiplier = 0.75; // Stricter for lower variance
        }

        // Calculate bounds
        if ($iqr > 0) {
            $upperBound = $q3 + $iqrMultiplier * $iqr;
            $lowerBound = max(0, $q1 - $iqrMultiplier * $iqr);
        } else {
            // Fallback if IQR is zero (all prices same)
            $stdDev = $stats['std_dev'];
            $upperBound = $median + 2 * $stdDev;
            $lowerBound = max(0, $median - 2 * $stdDev);
        }

        // Determine rating
        if ($price <= $median) {
            return ['rating' => 'best', 'score' => 3];
        } elseif ($price <= $upperBound) {
            return ['rating' => 'good', 'score' => 2];
        } else {
            return ['rating' => 'high', 'score' => 1];
        }
    }

    /**
     * Rate price for low variance markets (prices are very similar)
     */
    private function rateLowVariance(float $price, array $stats): array
    {
        $median = $stats['median'];
        $mean = $stats['mean'];
        $stdDev = $stats['std_dev'];
        
        // Use stricter thresholds when prices are clustered
        $reference = ($median + $mean) / 2; // Average of median and mean
        
        // Use standard deviation for tighter bounds
        $deviation = $price - $reference;
        $zScore = $stdDev > 0 ? ($deviation / $stdDev) : 0;

        if ($zScore <= -0.5) {
            // Significantly below reference
            return ['rating' => 'best', 'score' => 3];
        } elseif ($zScore <= 0.5) {
            // Near reference
            return ['rating' => 'good', 'score' => 2];
        } elseif ($zScore <= 1.5) {
            // Moderately above
            return ['rating' => 'good', 'score' => 2];
        } else {
            // Significantly above
            return ['rating' => 'high', 'score' => 1];
        }
    }

    /**
     * Rate price for high variance markets (prices are very spread out)
     */
    private function rateHighVariance(float $price, array $stats): array
    {
        $median = $stats['median'];
        $trimmedMean = $stats['trimmed_mean'];
        $allPrices = $stats['all_prices'] ?? [];
        
        // Use trimmed mean (less affected by outliers) and percentile rank
        $reference = ($median * 0.6) + ($trimmedMean * 0.4);
        
        // Calculate percentile rank of this price
        $percentileRank = $this->calculatePercentileRank($price, $allPrices);

        // More lenient thresholds for high variance
        if ($percentileRank <= 33) {
            // Bottom third
            return ['rating' => 'best', 'score' => 3];
        } elseif ($percentileRank <= 66) {
            // Middle third
            return ['rating' => 'good', 'score' => 2];
        } else {
            // Top third
            return ['rating' => 'high', 'score' => 1];
        }
    }

    /**
     * Calculate percentile rank of a value in a sorted array
     */
    private function calculatePercentileRank(float $value, array $sortedArray): float
    {
        $count = count($sortedArray);
        if ($count === 0) return 50;

        $belowCount = 0;
        $equalCount = 0;

        foreach ($sortedArray as $price) {
            if ($price < $value) {
                $belowCount++;
            } elseif ($price == $value) {
                $equalCount++;
            }
        }

        // Percentile rank formula
        return (($belowCount + 0.5 * $equalCount) / $count) * 100;
    }

    /**
     * Calculate percentile value from sorted array (robust implementation)
     */
    private function percentile(array $sorted, float $percentile): float
    {
        $count = count($sorted);
        if ($count === 0) {
            return 0;
        }
        
        if ($count === 1) {
            return $sorted[0];
        }

        $index = ($percentile / 100) * ($count - 1);
        $lower = (int)floor($index);
        $upper = (int)ceil($index);
        
        // Ensure indices are within bounds
        $lower = max(0, min($lower, $count - 1));
        $upper = max(0, min($upper, $count - 1));
        
        if ($lower == $upper) {
            return $sorted[$lower];
        }
        
        // Linear interpolation
        $weight = $index - $lower;
        return $sorted[$lower] + $weight * ($sorted[$upper] - $sorted[$lower]);
    }
}
