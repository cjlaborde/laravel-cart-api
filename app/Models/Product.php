<?php

namespace App\Models;

use App\Scoping\Scoper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;

    // When we look these up inside of our routes.
    // We will use the slug to return this.
    public function getRouteKeyName()
    {
        return 'slug';
    }

    # within every scope we get a Builder
    # we take that builder and take what we pass in and filter it further down
    # pass it the scopes we going to be using
    public function scopeWithScopes(Builder $builder, $scopes = [])
    {
        # we need to pass through the builder and pass in the scopes we want to use.
        return (new Scoper(request()))->apply($builder, $scopes);
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
