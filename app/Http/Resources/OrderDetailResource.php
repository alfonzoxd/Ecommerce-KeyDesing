<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'price_at_purchase' => (float) $this->price,
            'subtotal' => $this->quantity * $this->price,
            'product' => [
                'id' => $this->product_id,
                'name' => $this->product->name ?? 'Producto eliminado',
                'image' => $this->product->image_url ?? null,
            ],
        ];
    }
}
