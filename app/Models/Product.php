<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'subcategory_id', 'name', 'description', 'price',
        'features', 'specifications', 'stock', 'image_url'
    ];

    // Convierte automáticamente los JSON de la BD a Arrays de PHP y viceversa
    protected $casts = [
        'features' => 'array',
        'specifications' => 'array',
        'price' => 'decimal:2',
    ];

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    // Relación híbrida con MongoDB (Explicada más abajo)
    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id', 'id');
    }
}
