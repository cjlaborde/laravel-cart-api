<?php

namespace Tests\Unit\Cart;

use App\Cart\Cart;
use App\Cart\Money;
use App\Models\ProductVariation;
use App\Models\ShippingMethod;
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

    public function test_it_returns_a_money_instance_for_the_subtotal()
    {
        $cart = new Cart(
            $user = User::factory()->create()
        );

        $this->assertInstanceOf(Money::class, $cart->subtotal());
    }

    public function test_it_correct_subtotal()
    {
        $cart = new Cart(
            $user = User::factory()->create()
        );

        $user->cart()->attach(
            $product = ProductVariation::factory()->create([
                'price' => 1000
            ]), [
                'quantity' => 2
            ]
        );

        $this->assertEquals($cart->subtotal()->amount(), 2000);
    }

    public function test_it_returns_a_money_instance_for_the_total()
    {
        $cart = new Cart(
            $user = User::factory()->create()
        );

        $this->assertInstanceOf(Money::class, $cart->total());
    }

    public function test_it_syncs_the_cart_to_update_quantities()
    {
        $cart = new Cart(
            $user = User::factory()->create()
        );

        $product = ProductVariation::factory()->create();
        $anotherProduct = ProductVariation::factory()->create();

        $user->cart()->attach([
            $product->id => [
                'quantity' => 2
            ],
            $anotherProduct->id => [
                'quantity' => 2
            ],
        ]);

        $cart->sync(); // this will end having no quantity since we actually have not attach any stock to the product

        $this->assertEquals($user->fresh()->cart->first()->pivot->quantity, 0);
        // get you can get second product by targeting is position 1
        $this->assertEquals($user->fresh()->cart->get(1)->pivot->quantity, 0);
    }

    public function test_it_can_check_if_the_cart_has_changed_after_syncing()
    {
        $cart = new Cart(
            $user = User::factory()->create()
        );

        $product = ProductVariation::factory()->create();
        $anotherProduct = ProductVariation::factory()->create();

        $user->cart()->attach([
            $product->id => [
                'quantity' => 2
            ],
            $anotherProduct->id => [
                'quantity' => 0
            ],
        ]);

        $cart->sync(); // this will end having no quantity since we actually have not attach any stock to the product

        $this->assertTrue($cart->hasChanged());
    }

    public function test_it_can_return_the_correct_total_without_shipping()
    {
        $cart = new Cart(
            $user = User::factory()->create()
        );

        $user->cart()->attach(
            $product = ProductVariation::factory()->create([
                'price' => 1000
            ]), [
                'quantity' => 2
            ]
        );

        $this->assertEquals($cart->total()->amount(), 2000);
    }

    public function test_it_can_return_the_correct_total_with_shipping()
    {
        $cart = new Cart(
            $user = User::factory()->create()
        );

        $shipping = ShippingMethod::factory()->create([
            'price' => 1000
        ]);

        $user->cart()->attach(
            $product = ProductVariation::factory()->create([
                'price' => 1000
            ]), [
                'quantity' => 2
            ]
        );

        $cart = $cart->withShipping($shipping->id);

        $this->assertEquals($cart->total()->amount(), 3000);
    }

    public function test_it_returns_products_in_cart()
    {
        $cart = new Cart(
            $user = User::factory()->create()
        );

        $user->cart()->attach(
            $product = ProductVariation::factory()->create([
                'price' => 1000
            ]), [
                'quantity' => 2
            ]
        );

        $this->assertInstanceOf(ProductVariation::class, $cart->products()->first());
    }

    // Alternative Test not really necessary
    public function test_it_doesnt_change_the_cart()
    {
        $cart = new Cart(
            $user = User::factory()->create()
        );

        $cart->sync(); // this will end having no quantity since we actually have not attach any stock to the product

        $this->assertFalse($cart->hasChanged());
    }


}

