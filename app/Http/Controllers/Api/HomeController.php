<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         // كل الفئات
        $categories = Category::select('id', 'name', 'description')->get();

        // آخر المنتجات (آخر الأسعار المضافة أو المعدلة)
        $latestProducts = Product::orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        // العروض المحدودة
        $offers = Product::whereNotNull('offer_price')
            ->where('offer_expiry', '>', now()) // لسا العرض فعال
            ->take(10)
            ->get();

        return response()->json([
            'message'    => ApiMessage::HOMEPAGE_FETCHED->value,
            'categories' => $categories,
            'latest'     => $latestProducts,
            'offers'     => $offers,
        ]);
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

