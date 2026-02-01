<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\DashboardController;

// --- RUTAS PÚBLICAS (Cualquiera puede verlas) ---
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::post('webhooks/mercadopago', [WebhookController::class, 'handle']);
// Listar categorías es público
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{id}', [CategoryController::class, 'show']);

Route::get('subcategories', [SubcategoryController::class, 'index']);

Route::get('products', [ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'show']);

Route::get('products/{id}/reviews', [ReviewController::class, 'index']);

// --- RUTAS PROTEGIDAS ---
Route::middleware(['auth:api'])->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    Route::post('reviews', [ReviewController::class, 'store']);

    Route::post('orders', [OrderController::class, 'store']);
    Route::get('my-orders', [OrderController::class, 'myOrders']);
    // --- ZONA ADMIN ---
    Route::middleware(['is_admin'])->prefix('admin')->group(function () {

        // Gestión de Categorías (Solo Admin)
        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{id}', [CategoryController::class, 'update']);
        Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

        // Gestión de Subcategorías
        Route::post('subcategories', [SubcategoryController::class, 'store']);
        Route::put('subcategories/{id}', [SubcategoryController::class, 'update']);
        Route::delete('subcategories/{id}', [SubcategoryController::class, 'destroy']);

        // --- EN LA SECCIÓN ADMIN (Dentro del middleware 'is_admin') ---
        Route::post('products', [ProductController::class, 'store']);
        Route::put('products/{id}', [ProductController::class, 'update']);
        Route::delete('products/{id}', [ProductController::class, 'destroy']);

        Route::get('orders', [OrderController::class, 'index']); // Ver todas
        Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus']); // Cambiar estado

        Route::post('upload', [UploadController::class, 'upload']);

        Route::get('dashboard', [DashboardController::class, 'stats']);

    });
});
