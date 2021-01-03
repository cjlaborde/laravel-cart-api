<?php
namespace App\Cart\Payments;

use App\Models\PaymentMethod;

interface GatewayCustomer
{
    public function charge(PaymentMethod $card, $amount);
    // add card using token we get from server
    public function addCard($token);
    public function id();
}
