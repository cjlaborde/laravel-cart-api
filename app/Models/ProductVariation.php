<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariationType;

class ProductVariation extends Model
{
    use HasFactory;

    public function type()
    {
        // foreign key id and local key product_variation_type_id
        return $this->hasOne(ProductVariationType::class, 'id', 'product_variation_type_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
