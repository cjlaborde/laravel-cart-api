<?php

namespace Tests\Unit\Models\Orders;

use App\Models\Address;
use App\Models\Order;
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

        $this->assertInstanceOf(ShippingMethod::class, $order->ShippingMethod);
    }
}
