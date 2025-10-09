<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller {
    public function index() {
        return Store::with( [ 'user' ] )->paginate();
    }

    public function update( Request $request, Store $store ) {
        $request->validate( [
            'status' => 'required|in:active,inactive,pending',
        ] );
        $store->update( [ 'status' => $request->post( 'status' ) ] );
        return response()->json( [
            'message' => 'success',
            'store' => $store
        ] );
    }
}
