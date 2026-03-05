<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductCatalogController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/admin');
});

// ─── Auth ───
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::prefix('admin')->middleware(['auth', 'role:admin'])->name('admin.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::post('users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('shops/{id}/verify', [UserController::class, 'verifyShop'])->name('shops.verify');
    Route::post('distributors/{id}/verify', [UserController::class, 'verifyDistributor'])->name('distributors.verify');

    // Product Catalog
    Route::get('products', [ProductCatalogController::class, 'index'])->name('products.index');
    Route::get('products/create', [ProductCatalogController::class, 'create'])->name('products.create');
    Route::post('products', [ProductCatalogController::class, 'store'])->name('products.store');
    Route::get('products/{id}/edit', [ProductCatalogController::class, 'edit'])->name('products.edit');
    Route::put('products/{id}', [ProductCatalogController::class, 'update'])->name('products.update');
    Route::post('products/{id}/toggle', [ProductCatalogController::class, 'toggleStatus'])->name('products.toggle');

    // Categories
    Route::get('categories', [ProductCatalogController::class, 'categories'])->name('categories.index');
    Route::post('categories', [ProductCatalogController::class, 'storeCategory'])->name('categories.store');
    Route::put('categories/{id}', [ProductCatalogController::class, 'updateCategory'])->name('categories.update');
    Route::delete('categories/{id}', [ProductCatalogController::class, 'destroyCategory'])->name('categories.destroy');

    // Orders
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{id}', [OrderController::class, 'show'])->name('orders.show');

    // Disputes
    Route::get('disputes', [OrderController::class, 'disputes'])->name('disputes.index');
    Route::post('disputes/{id}/resolve', [OrderController::class, 'resolveDispute'])->name('disputes.resolve');

    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
});
