<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Opcional: Limpiamos el nombre antes de validar (Trim y Title Case)
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
        return [
            'subcategory_id' => 'required|exists:subcategories,id',
            // Agregamos 'unique:products,name'
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image_url' => 'nullable|url',
            'features' => 'nullable|array',
            'specifications' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Ya existe un producto con este nombre.',
        ];
    }
}
