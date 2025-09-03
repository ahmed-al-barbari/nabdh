<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Customer\SearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\Customer\CustomerController;
use App\Http\Controllers\Api\Merchant\OfferController;
use App\Http\Controllers\Api\Merchant\StoreController;
use App\Http\Controllers\Api\Customer\BarterController;
use App\Http\Controllers\Api\Customer\BarterMessageController;
use App\Http\Controllers\Api\Customer\FavoriteController;
use App\Http\Controllers\Api\Customer\NotificationController;
use App\Http\Controllers\Api\ProductController;
// use App\Http\Controllers\Api\Merchant\CategoryController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/user', function () {
        return request()->user();
    });


    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


    Route::middleware('auth:sanctum')->get('/user', function (Request $request) { });

    Route::get('/categories', [CategoryController::class, 'getCategories']);

    Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        // إدارة الأصناف (Categories)
        Route::apiResource('categories', CategoryController::class)->except(['show']);

        // إدارة المنتجات (Products)
        Route::apiResource('products', \App\Http\Controllers\Api\Admin\ProductController::class)->except(['show']);

        Route::get('/users', [AdminController::class, 'index']);
        Route::post('/users', [AdminController::class, 'store']);
        Route::get('/users/{id}', [AdminController::class, 'show']);
        Route::put('/users/{id}', [AdminController::class, 'update']);
        Route::patch('/users/{id}/status', [AdminController::class, 'updateStatus']);
        Route::delete('/users/{id}', [AdminController::class, 'destroy']);
    });


    Route::prefix('merchant')->middleware(['auth:sanctum', 'role:merchant'])->group(function () {

        Route::apiResource('stores', StoreController::class);

        // منتجات التاجر داخل متجره فقط
        Route::prefix('store/{store}/products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::post('/', [ProductController::class, 'store']);
            Route::put('/{product}', [ProductController::class, 'update']);
            Route::delete('/{product}', [ProductController::class, 'destroy']);
        });

        Route::post('/offers', [OfferController::class, 'store']);
        Route::put('/offers/{id}', [OfferController::class, 'update']);
        Route::delete('/offers/{id}', [OfferController::class, 'destroy']);
    });

    Route::middleware(['auth:sanctum', 'role:customer,merchant,admin'])->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications', [NotificationController::class, 'store']);
        Route::put('/notifications/{id}', [NotificationController::class, 'update']);
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
        // جلب كل الرسائل لمقايضة معينة
        Route::get('/barters/{barter_id}/messages', [BarterMessageController::class, 'index']);

        // إرسال رسالة جديدة في مقايضة معينة
        Route::post('/barters/{barter_id}/messages', [BarterMessageController::class, 'store']);
        Route::post('/barters', [BarterController::class, 'store']);
        //المفضلات
        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::post('/favorites/{productId}', [FavoriteController::class, 'store']);
        Route::delete('/favorites/{productId}', [FavoriteController::class, 'destroy']);
        Route::get('/search/stores', [SearchController::class, 'searchStores']);
    });
    Route::patch('/user/preferences', [CustomerController::class, 'updatePreferences']);
    Route::patch('/user/profile', [CustomerController::class, 'updateProfile']);

    Route::get('/barters', [BarterController::class, 'publicIndex']);
    Route::get('/barters/{id}', [BarterController::class, 'show']);
});
