<?php

namespace App\Cart\Payments\Gateways;

use App\Cart\Payments\Gateway;
use App\Cart\Payments\Gateways\StripeGatewayCustomer;
use App\Models\User;
// use alias so it doesn't interfere
use Stripe\Customer as StripeCustomer;

class StripeGateway implements Gateway
{
    protected $user;

    public function withUser(User $user)
    {
        $this->user = $user;

        // We need to return $this to be able to chain any other method.
        return $this;
    }

    public function user()
    {
        // grab our user record
        return $this->user;
    }

    // When we createCustomer we want to return our own customer Gateway that will then allow us to charge that
    // regardless of payment provider that we use
    public function createCustomer()
    {
        // we will implement method that will get customer from stripe, based on the user gateway_customer_id
        // If the customer already has id in database, we use same customer but add another card to the account
        // that we can verify in https://dashboard.stripe.com/test/customers/cus_IgUKHnTDgrYld1
        if ($this->user->gateway_customer_id) {
            return $this->getCustomer();
        }

        $customer = new StripeGatewayCustomer(
            // $this  = we pass the Gateway itself, so we can continue to access methods on the gateway
           // $this->createStripeCustomer(); pass through the created customer
            $this, $this->createStripeCustomer()
        );

        $this->user->update([
           'gateway_customer_id' => $customer->id()
        ]);

        return $customer;
        // create stripe customer here and return that as part of our stripe gateway customer object.

//        $customer = $this->createStripeCustomer();
//        dd($customer);
//        return new StripeGatewayCustomer();
        // we don't want to return the Stripe object itself
        // We want to wrap this up in our StripeGatewayCustomer.php
    }

    public function getCustomer()
    {
        return new StripeGatewayCustomer(
            $this, StripeCustomer::retrieve($this->user->gateway_customer_id)
        );
    }

    protected function createStripeCustomer()
    {
        return StripeCustomer::create([
            'email' => $this->user->email
        ]);
    }
}
