<?php

namespace App\Models;

use App\Cart\Money;
use App\Models\Traits\HasPrice\HasPrice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariationType;

class ProductVariation extends Model
{
    use HasFactory, HasPrice;

    public function getPriceAttribute($value)
    {
        if ($value === null) {
            // will return Money instance since you are using Attribute
            return $this->product->price;
        }
        return new Money($value);
    }

    public function priceVaries()
    {
        // this is not going to work since we got money instance for each
        // So what we need is to expose the amount in Money.php
//        return $this->price != $this->product->price;
//        To solve this we create amount function in Money.php
        return $this->price->amount() != $this->product->price->amount();
    }


    public function type()
    {
        // foreign key id and local key product_variation_type_id
        return $this->hasOne(ProductVariationType::class, 'id', 'product_variation_type_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}
