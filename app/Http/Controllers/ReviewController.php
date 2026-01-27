<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Product; // Modelo MySQL
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * PÚBLICO: Ver reseñas de un producto específico.
     * URL: /api/products/{id}/reviews
     */
    public function index($productId)
    {
        // Buscamos en MongoDB todas las reseñas donde product_id coincida
        // Importante: Casteamos a (int) para asegurar coincidencia con el ID de MySQL
        $reviews = Review::where('product_id', (int)$productId)
                         ->orderBy('created_at', 'desc')
                         ->get();

        return response()->json($reviews);
    }

    /**
     * CLIENTE: Crear una reseña.
     */
    public function store(Request $request)
    {
        // 1. Validaciones básicas
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 2. Validación Cruzada (MySQL): ¿El producto existe?
        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json(['error' => 'El producto no existe en nuestra base de datos SQL.'], 404);
        }

        // 3. Obtener usuario autenticado (desde el Token JWT)
        $user = Auth::user();

        // 4. Guardar en MongoDB
        // Guardamos el nombre del usuario aquí para evitar hacer consultas a MySQL cada vez que leamos reseñas (Desnormalización típica de NoSQL)
        $review = Review::create([
            'user_id' => $user->id,
            'user_name' => $user->name . ' ' . $user->last_name,
            'product_id' => (int)$request->product_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'created_at' => now(), // Mongo no siempre pone timestamps automáticos igual que SQL
        ]);

        return response()->json([
            'message' => 'Reseña publicada exitosamente',
            'review' => $review
        ], 201);
    }
}
