<?php
namespace App\Cart\Payments;

use App\Models\User;

interface Gateway
{
    // define the user
    public function withUser(User $user);
    // Create customer from user above
    // This will send all user information to Stripe
    public function createCustomer();

}
