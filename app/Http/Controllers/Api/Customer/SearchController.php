<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\MainProduct;
use App\Models\Store;
use App\Models\Product;
use App\Enums\ApiMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchController extends Controller
{

public function searchStores(Request $request, MainProduct $product)
{
    $perPage = $request->per_page ?? 10;
    $page = $request->input('page', 1);

    $lat = $request->latitude;
    $lng = $request->longitude;
    $productName = $request->product;
    $minPrice = $request->min_price;
    $maxPrice = $request->max_price;
    $filter = json_decode($request->filter);

    $storesQuery = Store::query()
        ->where('status', 'active')
        ->whereRelation('products', fn($q) => $q->where('product_id', $product->id))
        ->with([
            'city',
            'products' => fn($q) => $q->where('product_id', $product->id)
        ])
        ->when($productName, fn($q) => $q->whereHas('products', fn($p) => $p->where('name', 'like', "%{$productName}%")))
        ->when($minPrice, fn($q) => $q->whereHas('products', fn($p) => $p->where('price', '>=', $minPrice)))
        ->when($maxPrice, fn($q) => $q->whereHas('products', fn($p) => $p->where('price', '<=', $maxPrice)))
        ->select('*')
        ->selectRaw(
            "(6371 * acos(cos(radians(?)) * cos(radians(latitude))
              * cos(radians(longitude) - radians(?))
              + sin(radians(?)) * sin(radians(latitude)))) AS distance",
            [$lat, $lng, $lat]
        );

    $stores = $storesQuery->get()->unique('id')->values(); // ✅ إزالة التكرار

    // ترتيب حسب تقييم السعر لو المطلوب
    if (!empty($filter) && strtolower($filter->dependent ?? '') === 'rating') {
        $stores = $stores->map(function ($store) use ($product) {
            $productItem = $store->products->first();
            if (!$productItem) { $store->price_rating_score = 0; return $store; }

            $recentPrices = $productItem->recent_prices;
            if (!$recentPrices || $recentPrices->count() < 5) { $store->price_rating_score = 0; return $store; }

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
    }

    // Pagination
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
}



    // private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    // {
    //     $earthRadius = 6371; // بالكيلومتر

    //     $latFrom = deg2rad($lat1);
    //     $lonFrom = deg2rad($lon1);
    //     $latTo = deg2rad($lat2);
    //     $lonTo = deg2rad($lon2);

    //     $latDelta = $latTo - $latFrom;
    //     $lonDelta = $lonTo - $lonFrom;

    //     $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
    //         cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

    //     return round($angle * $earthRadius, 2); // المسافة بالكيلومتر
    // }
}
