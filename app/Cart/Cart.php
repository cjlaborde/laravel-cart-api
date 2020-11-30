<?php

namespace App\Cart;

use App\Models\User;

class Cart
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
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

    protected function getCurrentQuantity($productId )
    {
        // where comes from Collection methods
        if ($product = $this->user->cart->where('id', $productId)->first()) {
            return $product->pivot->quantity;
        }

        return 0;
    }
}
