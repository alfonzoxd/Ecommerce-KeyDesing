<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => 'required|exists:addresses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'address_id.required' => 'Debes seleccionar una dirección de envío.',
            'items.required' => 'El carrito no puede estar vacío.',
            'items.*.product_id.exists' => 'Uno de los productos seleccionados no existe.',
        ];
    }
}
