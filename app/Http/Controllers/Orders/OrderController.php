<?php

namespace App\Http\Controllers\Orders;

use App\Cart\Cart;
use App\Events\Order\OrderCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\OrderStoreRequest;
use App\Http\Resources\OrderResource;
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
        if ($cart->isEmpty()) {
            return response(null, 400);
        }

        $order = $this->createOrder($request, $cart);
//        dd(get_class($cart->products()));
//        dd($cart->products()->forSyncing());

        $order->products()->sync($cart->products()->forSyncing());
//        $order->load(['shippingMethod']);

        // Fire an event and process the payment first of all and then empty the cart
        event(new OrderCreated($order));

        return new OrderResource($order);
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
