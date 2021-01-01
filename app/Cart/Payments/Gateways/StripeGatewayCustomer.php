<?php

namespace App\Cart\Payments\Gateways;

use App\Cart\Payments\GatewayCustomer;
use App\Models\PaymentMethod;
use App\Models\User;

class StripeGatewayCustomer implements GatewayCustomer
{
    public function charge(PaymentMethod $card, $amount)
    {
        //
    }
    // add card using token we get from server
    public function addCard($token)
    {
        dd('add card');
    }
}
