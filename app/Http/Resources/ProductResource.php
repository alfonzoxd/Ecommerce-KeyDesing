<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price, // Casteamos a float
            'stock' => (int) $this->stock,
            'image_url' => $this->image_url,

            // Los campos JSON ya vienen convertidos a Array gracias al Model cast
            'features' => $this->features ?? [],
            'specifications' => $this->specifications ?? [],

            'created_at' => $this->created_at->format('Y-m-d'),

            // Relación: Si cargamos la subcategoría, mostramos sus datos básicos
            'subcategory' => $this->whenLoaded('subcategory', function () {
                return [
                    'id' => $this->subcategory->id,
                    'name' => $this->subcategory->name,
                    'category_name' => $this->subcategory->category->name ?? null, // Nombre del abuelo
                ];
            }),
        ];
    }
}
