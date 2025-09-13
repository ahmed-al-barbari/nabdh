<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Report;
use Auth;
use Exception;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return Report::with('product.store.user')->paginate();
    }
    public function store(Product $product)
    {
        try {

            $report = $product->report()->updateOrCreate(
                [],
                [
                    'count' => \DB::raw('COALESCE(count, 0) + 1'),
                    'status' => 'pending',
                ]
            );
            $report->users()->syncWithoutDetaching([Auth::id()]);
        } catch (Exception $e) {
            throw $e;
        }
        return $report;
    }
    public function update(Request $request, Product $product)
    {
        $product->report->update([
            'status' => 'reviewed',
        ]);
        return response()->json([
            'message' => 'تم تحويل الحالة بنجاحّ'
        ]);
    }
}
