<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateCategoryRequest extends FormRequest
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
        // Obtenemos el ID de la ruta (api/categories/{id})
        $id = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:20',
                // Ignoramos el ID actual para que no choque consigo mismo
                'unique:categories,name,' . $id,
                'regex:/^[a-zA-Z\s]+$/'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'El nombre solo puede contener letras y espacios.',
            'name.max' => 'El nombre no puede tener más de 25 caracteres.',
            'name.unique' => 'Esta categoría ya existe.',
        ];
    }
}
