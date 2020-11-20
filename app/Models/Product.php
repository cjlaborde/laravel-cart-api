<?php

namespace App\Models;

use App\Models\Traits\HasPrice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\CanBeScoped;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, CanBeScoped, HasPrice;

    // When we look these up inside of our routes.
    // We will use the slug to return this.
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function inStock()
    {
//        return (bool) $this->first()->pivot->in_stock;
        return $this->stockCount() > 0;
    }

    public function stockCount()
    {
        return $this->variations->sum(function ($variation) {
            return $variation->stockCount();
        });
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class)->orderBy('order', 'asc');
    }


}
