<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductIndexResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Scoping\Scopes\CategoryScope;

class ProductController extends Controller
{
    public function index()
    {
        # score by things we want to use
        # 'category' => new CategoryScope
        # 1) Get the value in request 'category'
        # 2) Pass it to new CategoryScope and before we get to next item it will be filtered down
        $products = Product::withScopes($this->scopes())->paginate(10);

        return ProductIndexResource::collection($products);
    }

    // http://cart-api.test/api/products/nike-air-max
    public function show(Product $product)
    {
        // ProductResource is a standard resource that extends ProductIndexResource
        return new ProductResource($product);
    }

    protected function scopes()
    {
        return [
            'category' => new CategoryScope()
        ];
    }
}
