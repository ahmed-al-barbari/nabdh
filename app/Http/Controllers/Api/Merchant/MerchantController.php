<?php

namespace App\Http\Controllers\Api\Merchant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Category;
use App\Models\Product;
use App\Models\Offer;

class MerchantController extends Controller
{
    public function addStore(Request $request) {
        $request->validate(['name'=>'required','description'=>'nullable']);
        $store = Store::create([
            'merchant_id'=>$request->user()->id,
            'name'=>$request->name,
            'description'=>$request->description
        ]);
        return response()->json($store);
    }

    public function addCategory(Request $request,$store_id) {
        $request->validate(['name'=>'required','description'=>'nullable']);
        $category = Category::create([
            'store_id'=>$store_id,
            'name'=>$request->name,
            'description'=>$request->description
        ]);
        return response()->json($category);
    }

    public function addProduct(Request $request,$category_id) {
        $request->validate(['name'=>'required','price'=>'required|numeric','stock'=>'required|integer']);
        $product = Product::create([
            'category_id'=>$category_id,
            'name'=>$request->name,
            'description'=>$request->description,
            'price'=>$request->price,
            'stock'=>$request->stock,
            'image'=>$request->image
        ]);
        return response()->json($product);
    }

    public function addOffer(Request $request,$product_id) {
        $request->validate([
            'title'=>'required',
            'discount_percentage'=>'required|numeric',
            'start_date'=>'required|date',
            'end_date'=>'required|date'
        ]);
        $offer = Offer::create([
            'product_id'=>$product_id,
            'title'=>$request->title,
            'discount_percentage'=>$request->discount_percentage,
            'start_date'=>$request->start_date,
            'end_date'=>$request->end_date
        ]);
        return response()->json($offer);
    }
}

