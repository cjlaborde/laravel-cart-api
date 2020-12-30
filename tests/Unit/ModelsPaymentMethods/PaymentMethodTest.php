<?php

namespace Tests\Unit\ModelsPaymentMethods;

use App\Models\PaymentMethod;
use App\Models\User;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    public function test_it_belongs_to_a_user()
    {
        $paymentMethod = PaymentMethod::factory()->create([
            'user_id' => User::factory()->create()->id
        ]);

        $this->assertInstanceOf(User::class, $paymentMethod->user);
    }
    public function test_it_sets_old_addresses_to_not_default_when_creating()
    {
        $user = User::factory()->create();

        $oldPaymentMethod = PaymentMethod::factory()->create([
            'default' => true,
            // we need to attach to same user or else test would fail
            'user_id' => $user->id
        ]);

        PaymentMethod::factory()->create([
            'default' => true,
            'user_id' => $user->id
        ]);

        $this->assertFalse($oldPaymentMethod->fresh()->default);
    }
}
