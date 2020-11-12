<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductIndexResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::paginate(10);

        return ProductIndexResource::collection($products);
    }

    // http://cart-api.test/api/products/nike-air-max
    public function show(Product $product)
    {
        // ProductResource is a standard resource that extends ProductIndexResource
        return new ProductResource($product);
    }
}
