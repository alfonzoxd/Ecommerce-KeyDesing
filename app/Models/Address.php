<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'street_address', // calle_avenida
        'number',
        'interior',
        'reference',
        'department',
        'province',
        'district',
        'zip_code'
    ];

    // Relación inversa: Una dirección pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Una dirección puede ser usada en muchas órdenes (histórico)
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
