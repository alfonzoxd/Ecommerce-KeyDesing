<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WebhookController;
// --- RUTAS PÚBLICAS (Cualquiera puede verlas) ---
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::post('webhooks/mercadopago', [WebhookController::class, 'handle']);
// Listar categorías es público
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{id}', [CategoryController::class, 'show']);

Route::get('subcategories', [App\Http\Controllers\SubcategoryController::class, 'index']);

Route::get('products', [App\Http\Controllers\ProductController::class, 'index']);
Route::get('products/{id}', [App\Http\Controllers\ProductController::class, 'show']);

Route::get('products/{id}/reviews', [App\Http\Controllers\ReviewController::class, 'index']);

// --- RUTAS PROTEGIDAS ---
Route::middleware(['auth:api'])->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    Route::post('reviews', [App\Http\Controllers\ReviewController::class, 'store']);

    Route::post('orders', [App\Http\Controllers\OrderController::class, 'store']);
    // --- ZONA ADMIN ---
    Route::middleware(['is_admin'])->prefix('admin')->group(function () {

        // Gestión de Categorías (Solo Admin)
        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{id}', [CategoryController::class, 'update']);
        Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

        // Gestión de Subcategorías
        Route::post('subcategories', [App\Http\Controllers\SubcategoryController::class, 'store']);
        Route::put('subcategories/{id}', [App\Http\Controllers\SubcategoryController::class, 'update']);
        Route::delete('subcategories/{id}', [App\Http\Controllers\SubcategoryController::class, 'destroy']);

        // --- EN LA SECCIÓN ADMIN (Dentro del middleware 'is_admin') ---
        Route::post('products', [App\Http\Controllers\ProductController::class, 'store']);
        Route::put('products/{id}', [App\Http\Controllers\ProductController::class, 'update']);
        Route::delete('products/{id}', [App\Http\Controllers\ProductController::class, 'destroy']);

    });
});
