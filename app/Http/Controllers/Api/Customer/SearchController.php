<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\MainProduct;
use App\Models\Store;
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

        // Get stores that have the specified product
        $stores = Store::whereRelation('products', function ($q) use ($product) {
            return $q->where('product_id', $product->id);
        })->with([
            'city',
            'products' => function ($q) use ($product) {
                $q->where('product_id', $product->id);
            }
        ])
            ->filter($filter, $product)
            ->get();

        // Apply price rating if rating filter is selected
        if (!empty($filter) && strtolower($filter->dependent ?? '') === 'rating') {
            $stores = $this->applyPriceRating($stores, $product);
        }

        // Paginate results
        $paginatedStores = new LengthAwarePaginator(
            $stores->forPage($page, $perPage),
            $stores->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // $stores = $storesQuery->get()->unique('id')->values(); // ✅ إزالة التكرار

        // ترتيب حسب تقييم السعر لو المطلوب
        if (!empty($filter) && strtolower($filter->dependent ?? '') === 'rating') {
            $stores = $stores->map(function ($store) use ($product) {
                $productItem = $store->products->first();
                if (!$productItem) {
                    $store->price_rating_score = 0;
                    return $store;
                }

                $recentPrices = $productItem->recent_prices;
                if (!$recentPrices || $recentPrices->count() < 5) {
                    $store->price_rating_score = 0;
                    return $store;
                }

                $sorted = $recentPrices->sort()->values()->all();
                $q1 = $this->percentile($sorted, 25);
                $median = $this->percentile($sorted, 50);
                $q3 = $this->percentile($sorted, 75);
                $iqr = $q3 - $q1;
                $upperBound = $q3 + $iqr;

                if ($productItem->price <= $median) $store->price_rating_score = 3;
                elseif ($productItem->price <= $upperBound) $store->price_rating_score = 2;
                else $store->price_rating_score = 1;
                return $store;
            });

            // فرز حسب score
            $stores = $stores->sortByDesc('price_rating_score')->values();
        } else {
            // فرز حسب المسافة إذا ما في فلتر تقييم
            $stores = $stores->sortBy('distance')->values();
            return response()->json([
                'message' => ApiMessage::STORES_FETCHED->value,
                'stores' => $paginatedStores,
            ]);
        }
    }

    /**
     * Apply price rating to stores based on median pricing analysis
     */
    private function applyPriceRating($stores, $product)
    {
        return $stores->map(function ($store) use ($product) {
            $productItem = $store->products->first();

            if (!$productItem) {
                $store->price_rating = 'no_rating';
                $store->price_rating_score = 0;
                return $store;
            }

            $recentPrices = $productItem->recent_prices;

            // Need at least 5 prices for meaningful analysis
            if (!$recentPrices || $recentPrices->count() < 5) {
                $store->price_rating = 'no_rating';
                $store->price_rating_score = 0;
                return $store;
            }

            $sorted = $recentPrices->sort()->values()->all();
            $median = $this->percentile($sorted, 50);
            $q3 = $this->percentile($sorted, 75);
            $iqr = $q3 - $this->percentile($sorted, 25);
            $upperBound = $q3 + $iqr;
            // Determine price rating
            if ($productItem->price <= $median) {
                $store->price_rating = 'best'; // عادل - Best price
                $store->price_rating_score = 3;
            } elseif ($productItem->price <= $upperBound) {
                $store->price_rating = 'good'; // جيد - Good price
                $store->price_rating_score = 2;
            } else {
                $store->price_rating = 'high'; // مرتفع - High price
                $store->price_rating_score = 1;
            }

            return $store;
        })->sortByDesc('price_rating_score')->values();
    }

    /**
     * Calculate percentile value from sorted array
     */
    private function percentile($sorted, $percentile)
    {
        $index = ($percentile / 100) * (count($sorted) - 1);
        $lower = floor($index);
        $upper = ceil($index);

        if ($lower == $upper) {
            return $sorted[$lower];
        }

        return $sorted[$lower] + ($index - $lower) * ($sorted[$upper] - $sorted[$lower]);
    }
}
