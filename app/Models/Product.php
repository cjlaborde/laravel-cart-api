<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // When we look these up inside of our routes.
    // We will use the slug to return this.
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
