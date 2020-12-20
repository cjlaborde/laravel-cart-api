<?php

namespace App\Http\Controllers\Cart;

use App\Cart\Cart;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\CartStoreRequest;
use App\Http\Requests\Cart\CartUpdateRequest;
use App\Http\Resources\Cart\CartResource;
use App\Models\ProductVariation;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    public function index(Request $request, Cart $cart)
    {
        $cart->sync();
        // reduce nb_statements
        $request->user()->load(['cart.product', 'cart.product.variations.stock', 'cart.stock', 'cart.type']);


        // reason we pass $request->user() is because of what we use inside our CartResource
        return (new CartResource($request->user()))
            ->additional([
                // $request to extract any information from query string
                'meta' => $this->meta($cart, $request)
            ]);

    }

    protected function meta(Cart $cart, Request $request)
    {
        return [
            'empty' => $cart->isEmpty(),
            'subtotal' => $cart->subtotal()->formatted(),
            'total' => $cart->withShipping($request->shipping_method_id)->total()->formatted(),
            'changed' => $cart->hasChanged()
        ];
    }

    // The middleware will only allow us to access user if that user is authenticated
    public function store(CartStoreRequest $request, Cart $cart)
    {
        $cart->add($request->products);
    }

    public function update(ProductVariation  $productVariation, CartUpdateRequest $request, Cart $cart)
    {
//        dd($productVariation);
        $cart->update($productVariation->id, $request->quantity);
    }

    public function destroy(ProductVariation  $productVariation, Cart $cart)
    {
        $cart->delete($productVariation->id);
    }
}
