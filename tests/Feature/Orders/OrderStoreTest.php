<?php

namespace Tests\Feature\Orders;

use App\Models\Address;
use App\Models\ShippingMethod;
use App\Models\User;
use Tests\TestCase;

class OrderStoreTest extends TestCase
{
    public function test_it_fails_if_not_authenticated()
    {
        $this->json('POST', 'api/orders')
            ->assertStatus(401);
    }

    public function test_it_requires_an_address()
    {
        $user = User::factory()->create();

        $this->jsonAs($user, 'POST', 'api/orders')
            ->assertJsonValidationErrors(['address_id']);
    }

    public function test_it_requires_an_address_that_exists()
    {
        $user = User::factory()->create();

        $this->jsonAs($user, 'POST', 'api/orders', [
            'address_id' => 1
        ])
            ->assertJsonValidationErrors(['address_id']);
    }

    public function test_it_requires_an_address_that_belongs_to_the_authenticated_user()
    {
        $user = User::factory()->create();

        // we create new random user here
        $address = Address::factory()->create([
            'user_id' => User::factory()->create()->id
        ]);

        $this->jsonAs($user, 'POST', 'api/orders', [
            'address_id' => $address->id
        ])
            ->assertJsonValidationErrors(['address_id']);
    }

    public function test_it_requires_a_shipping_method()
    {
        $user = User::factory()->create();

        $this->jsonAs($user, 'POST', 'api/orders')
            ->assertJsonValidationErrors(['shipping_method_id']);
    }

    public function test_it_requires_a_shipping_method_that_exists()
    {
        $user = User::factory()->create();

        $this->jsonAs($user, 'POST', 'api/orders', [
            'shipping_method_id' => 1
        ])
            ->assertJsonValidationErrors(['shipping_method_id']);
    }

    public function test_it_requires_a_shipping_method_valid_for_the_given_address()
    {
        $user = User::factory()->create();

        $address = Address::factory()->create([
            'user_id' => $user->id,
        ]);

        $shipping = ShippingMethod::factory()->create();

        $this->jsonAs($user, 'POST', 'api/orders', [
            'shipping_method_id' => $shipping->id,
            'address_id' => $address->id
        ])
            ->assertJsonValidationErrors(['shipping_method_id']);
    }

    public function test_it_can_create_an_order()
    {
        $user = User::factory()->create();

        // destructuring this and use it on our payload
        list($address, $shipping) = $this->orderDependencies($user);

//        dd($address); now you see address there with shipping as well

        $this->jsonAs($user, 'POST', 'api/orders', [
            'address_id' => $address->id,
            'shipping_method_id' => $shipping->id
        ]);

        // check the database has this information
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'address_id' => $address->id,
            'shipping_method_id' => $shipping->id
        ]);
    }

    protected function orderDependencies(User $user)
    {
        $address = Address::factory()->create([
            'user_id' => $user->id
        ]);

        $shipping = ShippingMethod::factory()->create();

        // we attach so that we know is valid address
        $shipping->countries()->attach($address->country);

        return [$address, $shipping];
    }
}
