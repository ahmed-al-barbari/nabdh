<?php

namespace App\Http\Controllers\Api;

use App\Models\Store;
use App\Models\Product;
use App\Enums\ApiMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
    public function searchProducts(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $query = $request->query;

        $products = Product::where('name', 'like', "%{$query}%")
            ->with(['category.store']) // نجيب المتجر مع الفئة
            ->get()
            ->map(function ($product) use ($request) {
                $store = $product->category->store;
                $product->store_name = $store->name;
                $product->store_address = $store->description;
                $product->distance = $this->calculateDistance(
                    $request->latitude,
                    $request->longitude,
                    $store->latitude,
                    $store->longitude
                );
                return $product;
            })
            ->sortBy('distance') // نرتب حسب الأقرب
            ->values();

        return response()->json([
            'message' => ApiMessage::PRODUCTS_FETCHED->value,
            'products' => $products
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // بالكيلومتر

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius, 2); // المسافة بالكيلومتر
    }
}
