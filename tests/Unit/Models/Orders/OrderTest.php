<?php

namespace Tests\Unit\Models\Orders;

use App\Cart\Money;
use App\Models\Address;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\ProductVariation;
use App\Models\ShippingMethod;
use App\Models\User;
use Tests\TestCase;

class OrderTest extends TestCase
{
    public function test_it_has_a_default_status_of_pending()
    {
        $order = Order::factory()->create([
            'user_id' => User::factory()->create()
        ]);

        $this->assertEquals($order->status, Order::PENDING);
    }

    public function test_it_belongs_to_a_user()
    {
        $order = Order::factory()->create([
            'user_id' => User::factory()->create()
        ]);

        $this->assertInstanceOf(User::class, $order->user);
    }

    public function test_it_belongs_to_an_address()
    {
        $order = Order::factory()->create([
            'user_id' => User::factory()->create()
        ]);

        $this->assertInstanceOf(Address::class, $order->address);
    }

    public function test_it_belongs_to_a_shipping_method()
    {
        $order = Order::factory()->create([
            'user_id' => User::factory()->create()
        ]);

        $this->assertInstanceOf(ShippingMethod::class, $order->shippingMethod);
    }

    public function test_it_belongs_to_a_payment_method()
    {
        $order = Order::factory()->create([
            'user_id' => User::factory()->create()
        ]);

        $this->assertInstanceOf(PaymentMethod::class, $order->paymentMethod);
    }

    public function test_it_has_many_products()
    {
        $order = Order::factory()->create([
            'user_id' => User::factory()->create()
        ]);

        $order->products()->attach(
            ProductVariation::factory()->create(), [
                'quantity' => 1
            ]
        );

        $this->assertInstanceOf(ProductVariation::class, $order->products->first());
    }

    public function test_it_has_a_quantity_attached_to_the_products()
    {
        $order = Order::factory()->create([
            'user_id' => User::factory()->create()
        ]);

        $order->products()->attach(
            ProductVariation::factory()->create(), [
                'quantity' => $quantity = 2
            ]
        );

        $this->assertEquals($order->products->first()->pivot->quantity, $quantity);
    }

    public function test_it_returns_a_money_instance_for_the_subtotal()
    {
        $order = Order::factory()->create([
            'user_id' => User::factory()->create()
        ]);

        $this->assertInstanceOf(Money::class, $order->subtotal);
    }

    public function test_it_returns_a_money_instance_for_the_total()
    {
        $order = Order::factory()->create([
            'user_id' => User::factory()->create()
        ]);

        $this->assertInstanceOf(Money::class, $order->total());
    }

    public function test_it_adds_shipping_onto_the_total()
    {
        $order = Order::factory()->create([
            'user_id' => User::factory()->create()->id,
            'subtotal' => 1000,
            'shipping_method_id' => ShippingMethod::factory()->create([
                'price' => 1000
            ])
        ]);

        $this->assertEquals( $order->total()->amount(), 2000);
    }
}


