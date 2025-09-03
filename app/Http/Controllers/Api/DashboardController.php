<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MainProduct;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'products_count' => MainProduct::query()->count(),
            'categories_count' => Category::query()->count(),
            'users_count' => User::query()->count(),
            'last_users_register' => User::query()->latest()->limit(5)->get(),

        ]);
    }
}
