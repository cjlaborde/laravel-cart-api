<?php

namespace Tests\Unit\Cart;

use App\Cart\Cart;
use App\Models\ProductVariation;
use App\Models\User;
use Tests\TestCase;

class CartTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_it_can_add_products_to_the_cart()
    {
        $cart = new Cart(
            $user = User::factory()->create()
        );

        $product = ProductVariation::factory()->create();

        $cart->add([
            ['id' => $product->id, 'quantity' => 1]
        ]);

        // grab user fresh out of the database, so that user would not have cart item in it
        // check if we have 1 item in the cart
        $this->assertCount(1, $user->fresh()->cart);
    }
}
