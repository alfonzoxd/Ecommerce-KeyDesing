<?php

namespace App\Models;

// ¡IMPORTANTE! Usar la clase de MongoDB, no la de SQL estándar
use MongoDB\Laravel\Eloquent\Model;

class Review extends Model
{
    // Definimos explícitamente la conexión
    protected $connection = 'mongodb';

    // Nombre de la colección en Mongo
    protected $collection = 'reviews';

    protected $fillable = [
        'user_id',
        'product_id',
        'user_name', // Guardamos el nombre para no hacer joins pesados
        'rating',    // 1 a 5
        'comment',
        'created_at'
    ];

    // Aunque sea Mongo, podemos definir relaciones "virtuales" con MySQL
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
