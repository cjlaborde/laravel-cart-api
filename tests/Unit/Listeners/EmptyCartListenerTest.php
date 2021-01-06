<?php

namespace Tests\Unit\Listeners;

use App\Cart\Cart;
use App\Listeners\Order\EmptyCart;
use App\Models\ProductVariation;
use App\Models\User;
use Tests\TestCase;

class EmptyCartListenerTest extends TestCase
{
    public function test_it_should_clear_the_cart()
    {
        $cart = new Cart(
            $user = User::factory()->create()
        );

        $user->cart()->attach(
            $product = ProductVariation::factory()->create()
        );

        $listener = new EmptyCart($cart);

        $listener->handle();

        $this->assertEmpty($user->cart);
    }
}
