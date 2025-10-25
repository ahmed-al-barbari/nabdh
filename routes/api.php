<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\Customer\SearchController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\Chat\ConversationController;
use App\Http\Controllers\Api\Chat\MessageController;
use App\Http\Controllers\Api\Customer\CustomerController;
use App\Http\Controllers\Api\Merchant\OfferController;
use App\Http\Controllers\Api\Merchant\StoreController;
use App\Http\Controllers\Api\Customer\BarterController;
use App\Http\Controllers\Api\Customer\BarterMessageController;
use App\Http\Controllers\Api\Customer\FavoriteController;
use App\Http\Controllers\Api\Customer\NotificationController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\GoogleController;

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

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::get('/search/stores/{product}', [SearchController::class, 'searchStores']);

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

Route::post('/verify-otp', [OtpController::class, 'verify']);
Route::get('/categories', [CategoryController::class, 'getCategories']);
Route::get('/products', [ProductController::class, 'getProducts']);

Route::get('/product/last-products', [ProductController::class, 'lastProduct']);
Route::get('/product/has-offer', [ProductController::class, 'productHasOffer']);
Route::get('/product/view/{product}', [ProductController::class, 'viewProduct']);
Route::get('/auth/user', function () {
    $user = request()->user();
    if (!$user) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }
    return $user->load(['store.city', 'city']);
})->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/active-notifications', [NotificationController::class, 'activeNotifications']);
    Route::patch('/notifications/mark-as-read', [NotificationController::class, 'markAsRead']);


    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::delete('user/delete-account', [AuthController::class, 'deleteAccount'])->middleware('auth:sanctum');


    Route::put('/barters/{barter}/mark-as-completed', [BarterController::class, 'markAsCompeleted'])->middleware('auth:sanctum');
    Route::post('/barters/{barter}/respond', [BarterController::class, 'respond'])->middleware('auth:sanctum');
    Route::post('/barters/{barter}/respond/{response}', [BarterController::class, 'acceptResponse'])->middleware('auth:sanctum');

    Route::get('/cities', [CityController::class, 'index']);
    Route::get('/cities/distances', [CityController::class, 'getDistances']);
    Route::put('/user', [UserController::class, 'update'])->middleware('auth:sanctum');

    Route::post('/products/{product}/report', [ReportController::class, 'store'])->middleware('auth:sanctum');

    Route::put('/merchant/store', [StoreController::class, 'updateGeneralSettingOrCreate'])->middleware('auth:sanctum');

    Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/stores', [\App\Http\Controllers\Admin\StoreController::class, 'index']);
        Route::put('/stores/{store}', [\App\Http\Controllers\Admin\StoreController::class, 'update']);
        Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index']);
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
        Route::get('/reports', [ReportController::class, 'index']);
        Route::put('/products/{product}/report/make-reviewed', [ReportController::class, 'update']);
    });

    Route::prefix('merchant')->middleware(['auth:sanctum', 'role:merchant'])->group(function () {

        Route::apiResource('stores', StoreController::class);

        // منتجات التاجر داخل متجره فقط
        Route::prefix('store/products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::post('/', [ProductController::class, 'store']);
            Route::get('/{product}', [ProductController::class, 'show']);
            Route::put('/{product}', [ProductController::class, 'update']);
            Route::delete('/{product}', [ProductController::class, 'destroy']);
        });

        Route::post('/offers', [OfferController::class, 'store']);
        Route::put('/offers/{id}', [OfferController::class, 'update']);
        Route::delete('/offers/{id}', [OfferController::class, 'destroy']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications', [NotificationController::class, 'store']);
        Route::put('/notifications/change-status-methods', [NotificationController::class, 'changeMethodStauts']);
        Route::put('/notifications/{id}', [NotificationController::class, 'update']);
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
        // جلب كل الرسائل لمقايضة معينة
        Route::get('/barters/{barter_id}/messages', [BarterMessageController::class, 'index']);

        // إرسال رسالة جديدة في مقايضة معينة
        Route::post('/barters/{barter_id}/messages', [BarterMessageController::class, 'store']);
        Route::post('/barters', [BarterController::class, 'store']);
        Route::put('/barters/{id}', [BarterController::class, 'update']);
        //المفضلات
        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::post('/favorites/{productId}', [FavoriteController::class, 'store']);
        Route::delete('/favorites/{productId}', [FavoriteController::class, 'destroy']);

        Route::patch('/user/profile', [CustomerController::class, 'updateProfile']);
    });
    Route::patch('/user/preferences', [CustomerController::class, 'updatePreferences']);
    
    Route::get('/barters', [BarterController::class, 'index']);
    Route::get('/barters/{id}', [BarterController::class, 'show']);
    Route::delete('/barters/{id}', [BarterController::class, 'destroy']);
    Broadcast::routes(['middleware' => ['auth:sanctum']]);
    Route::middleware(['auth:sanctum'])->group(function () {
        // بدء/جلب محادثة 1:1
        Route::post('/conversations/start', [ConversationController::class, 'start']);

        // رسائل المحادثة
        Route::get('/conversations/{id}/messages', [MessageController::class, 'index']);
        Route::post('/conversations/{id}/messages', [MessageController::class, 'store']);
    });
});
Route::middleware('auth:sanctum')->prefix('chat')->group(function () {
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'start']);
    Route::post('/messages/{id}/read', [MessageController::class, 'markAsRead']);
    Route::post('/messages/{conversation}', [MessageController::class, 'sendMessage']);
    Route::get('/conversations/{conversation}/get-messages', [MessageController::class, 'conversationMessages']);
    Route::get('/conversations/get-messages/{user}', [MessageController::class, 'index']);

});
