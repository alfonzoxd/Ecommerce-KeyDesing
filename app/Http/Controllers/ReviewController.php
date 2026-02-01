<?php

namespace App\Http\Controllers;

use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    use ResponseTrait;

    /**
     * PÚBLICO: Ver reseñas de un producto específico.
     * URL: /api/products/{id}/reviews
     */
    public function index($productId)
    {
        $reviews = Review::where('product_id', (int)$productId)
                         ->orderBy('created_at', 'desc')
                         ->get();
        return $this->responseJson(ReviewResource::collection($reviews));
    }

    /**
     * CLIENTE: Crear una reseña.
     */
    public function store(StoreReviewRequest $request)
    {
        // 1. Obtener usuario
        $user = Auth::user();

        // 2. Guardar en MongoDB
        $review = Review::create([
            'user_id' => $user->id,
            'user_name' => $user->name . ' ' . $user->last_name,
            'product_id' => (int)$request->product_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return $this->responseJsonMessageOk(
            'Reseña publicada exitosamente',
            new ReviewResource($review),
            201
        );
    }
}
