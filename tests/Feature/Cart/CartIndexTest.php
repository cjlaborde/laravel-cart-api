<?php

namespace Tests\Feature\Cart;

use App\Models\ProductVariation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CartIndexTest extends TestCase
{
    public function test_it_fails_if_unauthenticated()
    {
        $this->json('GET', 'api/cart')
            ->assertStatus(401);
    }

    public function test_it_shows_products_in_the_user_cart()
    {
        $user = User::factory()->create();

        $user->cart()->sync(
            $product = ProductVariation::factory()->create()
        );

        $this->jsonAs($user, 'GET', 'api/cart')
            ->assertJsonFragment([
                'id' => $product->id
            ]);
    }

    public function test_it_shows_if_the_cart_is_empty()
    {
        $user = User::factory()->create();

        $this->jsonAs($user, 'GET', 'api/cart')
            ->assertJsonFragment([
                'empty' => true
            ]);
    }

    public function test_it_shows_a_formatted_subtotal()
    {
        $user = User::factory()->create();

        $this->jsonAs($user, 'GET', 'api/cart')
            ->assertJsonFragment([
                'subtotal' => '$0.00'
            ]);
    }

    public function test_it_shows_a_formatted_total()
    {
        $user = User::factory()->create();

        $this->jsonAs($user, 'GET', 'api/cart')
            ->assertJsonFragment([
                'total' => '$0.00'
            ]);
    }
}
