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

    public function test_it_sets_old_addresses_to_not_default_when_creating()
    {
        $user = User::factory()->create();

        $oldAddress = Address::factory()->create([
           'default' => true,
            // we need to attach to same user or else test would fail
            'user_id' => $user->id
        ]);

        Address::factory()->create([
            'default' => true,
            'user_id' => $user->id
        ]);

        // What we checking is that when we create new address should go to default false
        $this->assertFalse($oldAddress->fresh()->default);
    }
}
