<?php

namespace App\Http\Requests\Subcategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreSubcategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->name) {
            $this->merge([
                'name' => Str::title(trim($this->name)), // "  teclados 60%  " -> "Teclados 60%"
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|integer|exists:categories,id', // El padre debe existir
            'name' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'unique:subcategories,name',
                'regex:/^[a-zA-Z0-9\s%\-]+$/' // Permitimos letras, números, espacios y % (ej: "60%")
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'La categoría seleccionada no existe.',
            'name.unique' => 'Esta subcategoría ya existe.',
        ];
    }
}
