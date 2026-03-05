<?php

use App\Http\Controllers\Api\AppController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CreditController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Molhanout B2B Marketplace
|--------------------------------------------------------------------------
*/

// ─── PUBLIC ROUTES ───
Route::prefix('v1')->group(function () {

    // Auth (rate limited)
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('auth/register', [AuthController::class, 'register']);
        Route::post('auth/login', [AuthController::class, 'login']);
    });

    // App config (theme, settings) — no auth needed
    Route::get('app/config', [AppController::class, 'config']);
    Route::get('app/theme', [AppController::class, 'theme']);

    // Public product catalog
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::get('categories', [ProductController::class, 'categories']);
});

// ─── AUTHENTICATED ROUTES ───
Route::prefix('v1')->middleware(['auth:sanctum', 'set_locale'])->group(function () {

    // Auth profile
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/profile', [AuthController::class, 'profile']);
    Route::put('auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('auth/fcm-token', [AuthController::class, 'updateFcmToken']);

    // Notifications
    Route::get('notifications', [AppController::class, 'notifications']);
    Route::put('notifications/{id}/read', [AppController::class, 'markNotificationRead']);
    Route::put('notifications/read-all', [AppController::class, 'markAllNotificationsRead']);

    // Image uploads (authenticated users)
    Route::post('images/upload', [ImageController::class, 'upload']);
    Route::post('images/products/{productId}', [ImageController::class, 'uploadProductImage']);
    Route::delete('images/{path}', [ImageController::class, 'delete'])->where('path', '.*');

    // ─── SHOP OWNER ROUTES ───
    Route::middleware('role:shop_owner')->prefix('shop')->group(function () {

        // Products — frequent suggestions
        Route::get('products/frequent', [ProductController::class, 'frequentProducts']);

        // Orders
        Route::get('orders', [OrderController::class, 'index']);
        Route::post('orders', [OrderController::class, 'store']);
        Route::get('orders/{id}', [OrderController::class, 'show']);
        Route::put('orders/{id}', [OrderController::class, 'update']);
        Route::post('orders/{id}/publish', [OrderController::class, 'publish']);
        Route::post('orders/{id}/cancel', [OrderController::class, 'cancel']);

        // Offers on orders
        Route::get('orders/{orderId}/offers', [OrderController::class, 'offers']);
        Route::post('orders/{orderId}/offers/{offerId}/accept', [OrderController::class, 'acceptOffer']);

        // Credit Management
        Route::get('customers', [CreditController::class, 'customers']);
        Route::post('customers', [CreditController::class, 'createCustomer']);
        Route::get('customers/{id}', [CreditController::class, 'showCustomer']);

        Route::post('credits', [CreditController::class, 'addCredit']);
        Route::post('credits/{creditId}/payment', [CreditController::class, 'addPayment']);
        Route::get('customers/{customerId}/transactions', [CreditController::class, 'transactions']);
    });

    // ─── DISTRIBUTOR ROUTES ───
    Route::middleware('role:distributor')->prefix('distributor')->group(function () {

        // Available orders
        Route::get('orders/available', [OfferController::class, 'availableOrders']);
        Route::get('orders/{id}', [OfferController::class, 'showOrder'])->where('id', '[0-9]+');

        // Offers
        Route::post('offers', [OfferController::class, 'store']);
        Route::get('offers', [OfferController::class, 'myOffers']);
        Route::get('offers/{id}', [OfferController::class, 'show']);

        // Deliveries
        Route::get('deliveries', [DeliveryController::class, 'index']);
        Route::get('deliveries/{id}', [DeliveryController::class, 'show']);
        Route::put('deliveries/{id}/status', [DeliveryController::class, 'updateStatus']);
        Route::post('deliveries/{id}/confirm', [DeliveryController::class, 'confirmWithPin']);
        Route::put('deliveries/{id}/location', [DeliveryController::class, 'updateLocation']);
    });
});
