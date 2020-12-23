<?php

namespace App\Http\Controllers\Orders;

use App\Cart\Cart;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\OrderStoreRequest;
use App\Models\ProductVariation;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $cart;

    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    public function store(OrderStoreRequest $request, Cart $cart)
    {
        $order = $this->createOrder($request, $cart);
//        dd(get_class($cart->products()));
//        dd($cart->products()->forSyncing());

        $order->products()->sync($cart->products()->forSyncing());
    }

    protected function createOrder(Request $request, Cart $cart)
    {
        return $request->user()->orders()->create(
            array_merge($request->only(['address_id', 'shipping_method_id']), [
                'subtotal' => $cart->subtotal()->amount()
            ])
        );
    }
}
