<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'total' => (float) $this->total,
            'shipping_cost' => (float) $this->shipping_cost,
            'payment_method' => $this->payment_method,
            'created_at' => $this->created_at->format('Y-m-d H:i'),
            // Relaciones condicionales (solo se muestran si se cargaron en el controlador)
            'address' => $this->whenLoaded('address'),
            'user' => new UserResource($this->whenLoaded('user')), // Reusamos UserResource
            'items' => OrderDetailResource::collection($this->whenLoaded('details')),
        ];
    }
}
