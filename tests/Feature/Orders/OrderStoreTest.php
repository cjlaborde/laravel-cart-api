<?php

namespace Tests\Feature\Orders;

use App\Events\Order\OrderCreated;
use App\Models\Address;
use App\Models\PaymentMethod;
use App\Models\ProductVariation;
use App\Models\ShippingMethod;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Support\Facades\Event;
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

        $user->cart()->sync(
            $product = $this->productWithStock()
        );

        $this->jsonAs($user, 'POST', 'api/orders')
            ->assertJsonValidationErrors(['address_id']);
    }

    public function test_it_requires_an_address_that_exists()
    {
        $user = User::factory()->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );

        $this->jsonAs($user, 'POST', 'api/orders', [
            'address_id' => 1
        ])
            ->assertJsonValidationErrors(['address_id']);
    }

    public function test_it_requires_an_address_that_belongs_to_the_authenticated_user()
    {
        $user = User::factory()->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );

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

        $user->cart()->sync(
            $product = $this->productWithStock()
        );

        $this->jsonAs($user, 'POST', 'api/orders')
            ->assertJsonValidationErrors(['shipping_method_id']);
    }

    public function test_it_requires_a_shipping_method_that_exists()
    {
        $user = User::factory()->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );

        $this->jsonAs($user, 'POST', 'api/orders', [
            'shipping_method_id' => 1
        ])
            ->assertJsonValidationErrors(['shipping_method_id']);
    }

    public function test_it_requires_a_shipping_method_valid_for_the_given_address()
    {
        $user = User::factory()->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );

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

    public function test_it_requires_a_payment_method()
    {
        $user = User::factory()->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );

        $this->jsonAs($user, 'POST', 'api/orders')
            ->assertJsonValidationErrors(['payment_method_id']);
    }

    public function test_it_requires_an_payment_method_that_belongs_to_the_authenticated_user()
    {
        $user = User::factory()->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );

        // we create new random user here
        $payment = PaymentMethod::factory()->create([
            'user_id' => User::factory()->create()->id
        ]);

        $this->jsonAs($user, 'POST', 'api/orders', [
            'address_id' => $payment->id
        ])
            ->assertJsonValidationErrors(['payment_method_id']);
    }

    public function test_it_can_create_an_order()
    {
        $user = User::factory()->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );

        // destructuring this and use it on our payload
        list($address, $shipping, $payment) = $this->orderDependencies($user);

//        dd($address); now you see address there with shipping as well

        $this->jsonAs($user, 'POST', 'api/orders', [
            'address_id' => $address->id,
            'shipping_method_id' => $shipping->id,
            'payment_method_id' => $payment->id
        ]);

        // check the database has this information
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'address_id' => $address->id,
            'shipping_method_id' => $shipping->id,
            'payment_method_id' => $payment->id,
        ]);
    }

    public function test_it_attaches_the_products_to_the_order()
    {
        $user = User::factory()->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );

        list($address, $shipping, $payment) = $this->orderDependencies($user);

        $response = $this->jsonAs($user, 'POST', 'api/orders', [
            'address_id' => $address->id,
            'shipping_method_id' => $shipping->id,
            'payment_method_id' => $payment->id,
        ]);

//        dd(json_decode($response->getContent())->data->id); // you get the id from the order id you created as part of the test

        $this->assertDatabaseHas('product_variation_order', [
            'product_variation_id' => $product->id,
            'order_id' => json_decode($response->getContent())->data->id
        ]);
    }

    public function test_it_fails_to_create_order_if_cart_is_empty()
    {
        $user = User::factory()->create();

        $user->cart()->sync([
            // grab the id only
            ($product = $this->productWithStock())->id => [
                'quantity' => 0
            ]
        ]);

//        dd($user->cart()->first()->pivot->quantity);

        list($address, $shipping, $payment) = $this->orderDependencies($user);

       $response = $this->jsonAs($user, 'POST', 'api/orders', [
            'address_id' => $address->id,
            'shipping_method_id' => $shipping->id,
            'payment_method_id' => $payment->id,
        ])
           ->assertStatus(400);
    }

    public function test_it_fires_an_order_created_event()
    {
        Event::fake();

        $user = User::factory()->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );

        list($address, $shipping, $payment) = $this->orderDependencies($user);

        $response = $this->jsonAs($user, 'POST', 'api/orders', [
            'address_id' => $address->id,
            'shipping_method_id' => $shipping->id,
            'payment_method_id' => $payment->id
        ]);

        Event::assertDispatched(OrderCreated::class, function ($event) use ($response) {
            return $event->order->id === json_decode($response->getContent())->data->id;
        });
    }

    public function test_it_empties_the_cart_when_ordering()
    {
        $user = User::factory()->create();

        $user->cart()->sync(
            $product = $this->productWithStock()
        );

        list($address, $shipping, $payment) = $this->orderDependencies($user);

        $response = $this->jsonAs($user, 'POST', 'api/orders', [
            'address_id' => $address->id,
            'shipping_method_id' => $shipping->id,
            'payment_method_id' => $payment->id
        ]);

//        dd($response->getContent());

//        dd($user->cart);
        // check if cart is empty
        $this->assertEmpty($user->cart);
    }

    protected function productWithStock()
    {
        $product = ProductVariation::factory()->create();

        Stock::factory()->create([
            'product_variation_id' => $product->id
        ]);

        return $product;
    }

    protected function orderDependencies(User $user)
    {
        $stripeCustomer = \Stripe\Customer::create([
            'email' => $user->email,
        ]);

//        dd($stripeCustomer->id);

        $user->update([
            'gateway_customer_id' => $stripeCustomer->id
        ]);

//        dd($user);

        $address = Address::factory()->create([
            'user_id' => $user->id
        ]);

        $shipping = ShippingMethod::factory()->create();

        // we attach so that we know is valid address
        $shipping->countries()->attach($address->country);

        $payment = PaymentMethod::factory()->create([
            'user_id' => $user->id
        ]);

        return [$address, $shipping, $payment];
    }
}
