<?php

namespace Tests\Unit\Cart;

use App\Cart\Cart;
use App\Models\ProductVariation;
use App\Models\User;
use Tests\TestCase;

class CartTest extends TestCase
{
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

    public function test_it_increments_quantity_where_adding_more_products()
    {
        $product = ProductVariation::factory()->create();

        // In here we simulating 2 different requests.
        $cart = new Cart(
            $user = User::factory()->create()
        );

        $cart->add([
            ['id' => $product->id, 'quantity' => 1]
        ]);

        $cart = new Cart($user->fresh());

        $cart->add([
            ['id' => $product->id, 'quantity' => 1]
        ]);

        // grab user fresh out of the database, so that user would not have cart item in it
        // check if we have 1 item in the cart
        $this->assertEquals($user->fresh()->cart->first()->pivot->quantity, 2);
    }

    public function test_it_can_update_quantities_in_the_cart()
    {
        $cart = new Cart(
            $user = User::factory()->create()
        );

        $user->cart()->attach(
            $product = ProductVariation::factory()->create(), [
                'quantity' => 1
            ]
        );

        $cart->update($product->id, 2);

        $this->assertEquals($user->fresh()->cart->first()->pivot->quantity, 2 );
    }

    public function test_it_can_delete_a_product_from_the_cart()
    {
        $cart = new Cart(
            $user = User::factory()->create()
        );

        $user->cart()->attach(
            $product = ProductVariation::factory()->create(), [
                'quantity' => 1
            ]
        );

        $cart->delete($product->id);

        $this->assertCount(0,$user->fresh()->cart);
    }

    public function test_it_can_empty_the_cart()
    {
        $cart = new Cart(
            $user = User::factory()->create()
        );

        $user->cart()->attach(
            $product = ProductVariation::factory()->create()
        );

        $cart->empty();

        $this->assertCount(0,$user->fresh()->cart);
    }

    public function test_it_can_check_if_the_cart_is_empty_of_quantities()
    {
        $cart = new Cart(
            $user = User::factory()->create()
        );

        $user->cart()->attach(
            $product = ProductVariation::factory()->create(), [
                'quantity' => 0
            ]
        );

        $this->assertTrue($cart->isEmpty());
    }
}

