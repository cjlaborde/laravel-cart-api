<?php

namespace Tests\Feature\Cart;

use App\Models\ProductVariation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CartStoreTest extends TestCase
{
    public function test_it_fails_if_unauthenticated()
    {
        $response = $this->json('POST', 'api/cart')
            ->assertStatus(401);

        // see output from the failure in the terminals to help us find out why test failed before
//        dd($response->getContent());
    }

    public function test_it_requires_products()
    {
        $user = User::factory()->create();
        $this->jsonAs($user,'POST', 'api/cart')
            ->assertJsonValidationErrors(['products']);
    }

    public function test_it_requires_products_to_be_an_array()
    {
        $user = User::factory()->create();

        $this->jsonAs($user,'POST', 'api/cart', [
            'product' => 1
        ])
            ->assertJsonValidationErrors(['products']);
    }

    public function test_it_requires_products_to_have_an_id()
    {
        $user = User::factory()->create();

        $this->jsonAs($user,'POST', 'api/cart', [
            'products' => [
                ['quantity' => 1]
            ]
        ])
            // asset if first product (0) must have an id
            ->assertJsonValidationErrors(['products.0.id']);
    }

    public function test_it_requires_products_to_exists()
    {
        $user = User::factory()->create();

        $this->jsonAs($user,'POST', 'api/cart', [
            'products' => [
                ['id' => 1, 'quantity' => 1]
            ]
        ])
            ->assertJsonValidationErrors(['products.0.id']);
    }

    public function test_it_requires_products_quantity_to_be_numeric()
    {
        $user = User::factory()->create();

        $this->jsonAs($user,'POST', 'api/cart', [
            'products' => [
                ['id' => 1, 'quantity' => 'one']
            ]
        ])
            ->assertJsonValidationErrors(['products.0.quantity']);
    }

    public function test_it_requires_products_quantity_to_be_at_least_one()
    {
        $user = User::factory()->create();

        $this->jsonAs($user,'POST', 'api/cart', [
            'products' => [
                ['id' => 1, 'quantity' => 0]
            ]
        ])
            ->assertJsonValidationErrors(['products.0.quantity']);
    }

    public function test_it_can_add_products_to_the_users_cart()
    {
        $user = User::factory()->create();

        $product = ProductVariation::factory()->create();

        $this->jsonAs($user,'POST', 'api/cart', [
            'products' => [
                ['id' => $product->id, 'quantity' => 2]
            ]
        ]);

        $this->assertDatabaseHas('cart_user', [
            'product_variation_id' => $product->id,
            'quantity' => 2
        ]);
    }
}
