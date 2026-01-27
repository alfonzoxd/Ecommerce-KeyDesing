<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    // Desactivamos updated_at porque un movimiento es un registro histórico, no se edita
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'user_id', 'type', 'quantity', 'reason', 'created_at'
    ];

    // Forzamos que created_at se llene automáticamente
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? now();
        });
    }
}
