<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // MongoDB usa _id, pero lo mapeamos a 'id' para consistencia
            'id' => $this->id,
            'product_id' => (int) $this->product_id,
            'user_name' => $this->user_name, // Datos denormalizados
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            // Formateamos fecha, asegurándonos que sea un objeto Carbon
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i') : null,
        ];
    }
}
