<?php

namespace App\Http\Requests\Subcategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateSubcategoryRequest extends FormRequest
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
        $id = $this->route('id'); // ID de la URL

        return [
            'category_id' => 'sometimes|integer|exists:categories,id',
            'name' => [
                'sometimes',
                'string',
                'min:3',
                'max:50',
                'unique:subcategories,name,' . $id,
                'regex:/^[a-zA-Z0-9\s%\-]+$/'
            ],
        ];
    }
}
