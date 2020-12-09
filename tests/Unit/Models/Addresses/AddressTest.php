<?php

namespace Tests\Unit\Models\Addresses;

use App\Models\Address;
use App\Models\Country;
use App\Models\User;
use Tests\TestCase;

class AddressTest extends TestCase
{
    public function test_it_has_one_country()
    {
        // address must belong to a User
        $address = Address::factory()->create([
            'user_id' => User::factory()->create()->id
        ]);
        // no need to add Country factory since is already created in the AddressFactory

        $this->assertInstanceOf(Country::class, $address->country);
    }

    public function test_it_belongs_to_a_user()
    {
        // address must belong to a User
        $address = Address::factory()->create([
            'user_id' => User::factory()->create()->id
        ]);
        // no need to add Country factory since is already created in the AddressFactory

        $this->assertInstanceOf(User::class, $address->user);
    }
}
