<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:2048'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Debes seleccionar una imagen.',
            'image.image' => 'El archivo debe ser una imagen válida.',
            'image.mimes' => 'Solo se permiten formatos: jpeg, png, jpg, webp.',
            'image.max' => 'La imagen no debe pesar más de 2MB.',
        ];
    }
}
