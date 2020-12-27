<?php

namespace Tests\Feature\Orders;


use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderIndexTest extends TestCase
{
    public function test_it_fails_if_user_isnt_authenticated()
    {
        $this->json('GET', 'api/orders')
            ->assertStatus(401);
    }

    public function test_it_returns_a_collection_of_orders()
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id
        ]);

        $this->jsonAs($user, 'GET', 'api/orders')
            ->assertJsonFragment([
                'id' => $order->id
            ]);
    }

    public function test_it_orders_by_the_latest_first()
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id
        ]);

        $anotherOrder = Order::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDay()
        ]);

        $this->jsonAs($user, 'GET', 'api/orders')
            // check if whar we see here are in correct order within the json response
            ->assertSeeInOrder([
                // if we put these 2 lines of code in wrong order below an error going to happen since it means they not in right order
              $order->created_at->toDateTimeString(),
              $anotherOrder->created_at->toDateTimeString(),
            ]);
    }

    public function test_it_has_pagination()
    {
        $user = User::factory()->create();

        $this->jsonAs($user, 'GET', 'api/orders')
            ->assertJsonStructure([
                'links',
                'meta'
            ]);


    }
}
