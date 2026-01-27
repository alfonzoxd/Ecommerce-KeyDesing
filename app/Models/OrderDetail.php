<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price' // Guardamos el precio histórico al momento de la compra
    ];

    // Relación: Pertenece a una orden
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Relación: Hace referencia a un producto
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
