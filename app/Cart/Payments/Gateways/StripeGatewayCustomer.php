<?php

namespace App\Cart\Payments\Gateways;

use App\Cart\Payments\Gateway;
use App\Cart\Payments\GatewayCustomer;
use App\Exceptions\PaymentFailedException;
use App\Models\PaymentMethod;
use App\Models\User;
use Exception;
use Stripe\Customer as StripeCustomer;
use Stripe\Charge as StripeCharge;

class StripeGatewayCustomer implements GatewayCustomer
{
    protected $gateway;
    protected $customer;

    public function __construct(Gateway $gateway, StripeCustomer $customer)
    {
        $this->gateway = $gateway;
        $this->customer = $customer;


    }

    public function charge(PaymentMethod $card, $amount)
    {
        try {
//            throw new PaymentFailedException();

            StripeCharge::create([
                'currency' => 'usd',
                'amount' => $amount,
                'customer' => $this->customer->id,
                'source' => $card->provider_id

            ]);
        } catch (Exception $e) {
            throw new PaymentFailedException();
        }
    }
    // add card using token we get from server
    public function addCard($token)
    {
        // https://stripe.com/docs/api/cards/create
        $card = $this->customer->createSource(
            $this->customer->id,
            ['source' => $token]
        );

        // https://stripe.com/docs/api/customers/update
        $this->customer->default_source = $card->id;
        $this->customer->save();

//        dd($card->brand);
        // reference gateway and grab user from gateway
        return $this->gateway->user()->paymentMethods()->create([
            'provider_id' => $card->id,
            'card_type' => $card->brand,
            'last_four' => $card->last4,
            'default' => true
        ]);

//        dd($card);
    }

    public function id()
    {
        return $this->customer->id;
    }
}
