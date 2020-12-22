<?php

namespace App\Cart;

use App\Models\ShippingMethod;
use App\Models\User;

class Cart
{
    protected $user;


    protected $changed = false;

    protected $shipping;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function products()
    {
        return $this->user->cart;
    }

    public function withShipping($shippingId)
    {
        // find it by the shipping ID that has been provided.
        $this->shipping = ShippingMethod::find($shippingId);

        return $this;
    }

    public function add($products)
    {
        // syncWithoutDetaching when we sync thing we essentially create what we passing in
        // basically what ever product variation we chosen we going to add them in there
        // WithoutDetaching it doesnt detach previous items
        // similar to attach but will sync things that were not already existing
        $this->user->cart()->syncWithoutDetaching(
            $this->getStorePayload($products)
        );
    }

    public function update($productId, $quantity)
    {
        $this->user->cart()->updateExistingPivot($productId, [
            'quantity' => $quantity
        ]);
    }

    public function delete($productId)
    {
        $this->user->cart()->detach($productId);
    }

    public function sync()
    {
//        return; # used to debug with laravel debug bar
        $this->user->cart->each(function ($product) {
            // Grab minimum quantity that is available
            $quantity = $product->minStock($product->pivot->quantity);
//             dd($quantity);

            // tell users if this change have happened to their cart
            $this->changed = $quantity != $product->pivot->quantity;

            // update pivot: it will change cart stock amount to the available stock amount when you add more items than currently in stock
            $product->pivot->update([
                'quantity' => $quantity
            ]);
        });
    }

    public function hasChanged()
    {
        return $this->changed;
    }

    // remove all products from the cart
    public function empty()
    {
        $this->user->cart()->detach();
    }

    public function isEmpty()
    {
        return $this->user->cart->sum('pivot.quantity') === 0;
    }

    public function subtotal()
    {
        // Access each product we have in cart and multiply the price with quantity and sum them up.
        $subtotal = $this->user->cart->sum(function ($product) {
            return $product->price->amount() * $product->pivot->quantity;
        });

        return new Money($subtotal);
    }

    public function total()
    {
        // check if there is a shipping object and add it
        if ($this->shipping) {
            return $this->subtotal()->add($this->shipping->price);
        }

        return $this->subtotal();
    }

    public function getStorePayload($products)
    {
        // collect our products to put them into laravel collection
        // Then use the id as the key
        // now we map through them
        return collect($products)->keyBy('id')->map(function ($product) {
            return [
                'quantity' => $product['quantity'] + $this->getCurrentQuantity($product['id'])
            ];
            // toArray since you can't sync in a collection (->syncWithoutDetaching)
        })->toArray();

//        dd($products);
    }

    protected function getCurrentQuantity($productId)
    {
        // where comes from Collection methods
        if ($product = $this->user->cart->where('id', $productId)->first()) {
            return $product->pivot->quantity;
        }

        return 0;
    }
}
