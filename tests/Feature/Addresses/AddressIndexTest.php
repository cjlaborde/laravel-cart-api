<?php

namespace Tests\Feature\Addresses;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddressIndexTest extends TestCase
{
    public function test_it_fails_if_not_authenticated()
    {
        $this->json('GET', 'api/addresses')
            ->assertStatus(401);
    }

    public function test_it_shows_addresses()
    {
        $user = User::factory()->create();

        $address = Address::factory()->create([
           'user_id' => $user->id
        ]);


        $this->jsonAs($user,'GET', 'api/addresses')
            ->assertJsonFragment([
                'id' => $address->id
            ]);
    }
}
