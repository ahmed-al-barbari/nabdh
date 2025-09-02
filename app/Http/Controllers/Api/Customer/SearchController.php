<?php

namespace App\Http\Controllers\Api;

use App\Models\Store;
use App\Models\Product;
use App\Enums\ApiMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
    public function searchStores(Request $request)
    {
        $request->validate([
            'latitude'   => 'required|numeric',
            'longitude'  => 'required|numeric',
            'product'    => 'nullable|string',
            'min_price'  => 'nullable|numeric|min:0',
            'max_price'  => 'nullable|numeric|min:0',
            'per_page'   => 'nullable|integer|min:1'
        ]);

        $lat = $request->latitude;
        $lng = $request->longitude;
        $productName = $request->product;
        $minPrice = $request->min_price;
        $maxPrice = $request->max_price;
        $perPage = $request->per_page ?? 10;

        $stores = Store::query()
            ->where('status', 'active')
            ->when($productName, function ($q) use ($productName) {
                $q->whereHas('products', function ($p) use ($productName) {
                    $p->where('name', 'like', "%{$productName}%");
                });
            })
            ->when($minPrice, function ($q) use ($minPrice) {
                $q->whereHas('products', function ($p) use ($minPrice) {
                    $p->where('price', '>=', $minPrice);
                });
            })
            ->when($maxPrice, function ($q) use ($maxPrice) {
                $q->whereHas('products', function ($p) use ($maxPrice) {
                    $p->where('price', '<=', $maxPrice);
                });
            })
            ->select('*')
            ->selectRaw(
                "(6371 * acos(cos(radians(?)) * cos(radians(latitude))
                    * cos(radians(longitude) - radians(?))
                    + sin(radians(?)) * sin(radians(latitude)))) AS distance",
                [$lat, $lng, $lat]
            )
            ->orderBy('distance', 'asc')
            ->paginate($perPage);

        return response()->json([
            'message' => ApiMessage::STORES_FETCHED->value,
            'stores'  => $stores
        ]);
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
