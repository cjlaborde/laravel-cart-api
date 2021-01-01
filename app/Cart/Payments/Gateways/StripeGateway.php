<?php

namespace App\Cart\Payments\Gateways;

use App\Cart\Payments\Gateway;
use App\Cart\Payments\Gateways\StripeGatewayCustomer;
use App\Models\User;

class StripeGateway implements Gateway
{
    protected $user;

    public function withUser(User $user)
    {
        $this->user = $user;

        // We need to return $this to be able to chain any other method.
        return $this;
    }

    // When we createCustomer we want to return our own customer Gateway that will then allow us to charge that
    // regardless of payment provider that we use
    public function createCustomer()
    {
        return new StripeGatewayCustomer();
    }
}
