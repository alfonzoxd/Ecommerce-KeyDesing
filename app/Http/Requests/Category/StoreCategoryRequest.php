<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Aquí limpiamos los datos (Trim y Capitalizar).
    protected function prepareForValidation()
    {
        if ($this->name) {
            $this->merge([
                'name' => Str::title(trim($this->name)), // "  gaming  " -> "Gaming"
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',    // Mínimo 3 letras
                'max:25',   // Máximo 25
                'unique:categories,name',
                // Regex: Solo letras (a-z) y espacios (\s).
                // ^ inicio, $ fin. i para case-insensitive.
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
