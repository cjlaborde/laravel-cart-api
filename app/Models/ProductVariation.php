<?php

namespace App\Models;

use App\Cart\Money;
use App\Models\Traits\HasPrice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    // Takes an amount that we set example 200 and then return 10 since is the only available current stock
    public function minStock($count)
    {
        return min($this->stockCount(), $count);
    }

    public function priceVaries()
    {
        // this is not going to work since we got money instance for each
        // So what we need is to expose the amount in Money.php
//        return $this->price != $this->product->price;
//        To solve this we create amount function in Money.php
        return $this->price->amount() != $this->product->price->amount();
    }

    public function inStock()
    {
        return $this->stockCount() > 0;
    }

    public function stockCount()
    {
        return $this->stock->sum('pivot.stock');
    }

    # Keep relationship methods in bottom and keep everything else at the top
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

    public function stock()
    {
        // what we want to get back from this relationship is a product variation instance
        // we not interested in the product variation what we are interested is the pivot information the stock
        // Reason we use belongsToMany is that we can access that pivot information
        return $this->belongsToMany(
            ProductVariation::class, 'product_variation_stock_view'
        )
            ->withPivot(
                'stock',
                'in_stock'
            );
    }
}
