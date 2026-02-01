<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->name) {
            $this->merge([
                'name' => Str::title(trim($this->name)),
            ]);
        }
    }

    public function rules(): array
    {
        // Obtenemos el ID del producto desde la URL
        // Asumiendo que tu ruta es /api/admin/products/{id}
        $id = $this->route('id');

        return [
            'subcategory_id' => 'sometimes|exists:subcategories,id',
            // Agregamos la validación unique ignorando el ID actual
            'name' => 'sometimes|string|max:255|unique:products,name,' . $id,
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'image_url' => 'nullable|url',
            'features' => 'nullable|array',
            'specifications' => 'nullable|array',
        ];
    }
}
