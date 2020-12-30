<?php

namespace Tests\Feature\PaymentMethods;

use App\Models\PaymentMethod;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaymentMethodIndexTest extends TestCase
{
    public function test_it_fails_if_not_authenticated()
    {
        $this->json('GET', 'api/payment-methods')
            ->assertStatus(401);
    }

    public function test_it_returns_a_collection_of_payment_methods()
    {
        $user = User::factory()->create();

        $payment = PaymentMethod::factory()->create([
            'user_id' => $user->id
        ]);

        $this->jsonAs($user, 'GET', 'api/payment-methods')
            ->assertJsonFragment([
                'id' => $payment->id
            ]);
    }
}
