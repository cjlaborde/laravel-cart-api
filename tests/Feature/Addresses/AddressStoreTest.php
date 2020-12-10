<?php

namespace Tests\Feature\Addresses;

use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Constraint\Count;
use Tests\TestCase;

class AddressStoreTest extends TestCase
{
    public function test_it_fails_if_authenticated()
    {
        $this->json('POST', 'api/addresses')
            ->assertStatus(401);
    }

    public function test_it_requires_a_name()
    {
        $user = User::factory()->create();

        $this->jsonAs($user, 'POST', 'api/addresses')
            ->assertJsonValidationErrors(['name']);
    }

    public function test_it_requires_address_line_one()
    {
        $user = User::factory()->create();

        $this->jsonAs($user, 'POST', 'api/addresses')
            ->assertJsonValidationErrors(['address_1']);
    }

    public function test_it_requires_a_city()
    {
        $user = User::factory()->create();

        $this->jsonAs($user, 'POST', 'api/addresses')
            ->assertJsonValidationErrors(['city']);
    }

    public function test_it_requires_a_postal_code()
    {
        $user = User::factory()->create();

        $this->jsonAs($user, 'POST', 'api/addresses')
            ->assertJsonValidationErrors(['postal_code']);
    }

    public function test_it_requires_a_country()
    {
        $user = User::factory()->create();

        $this->jsonAs($user, 'POST', 'api/addresses')
            ->assertJsonValidationErrors(['country_id']);
    }

    public function test_it_requires_a_valid_country()
    {
        $user = User::factory()->create();

        $this->jsonAs($user, 'POST', 'api/addresses', [
            'country_id' => 1
        ])
            ->assertJsonValidationErrors(['country_id']);
    }

    public function test_it_stores_an_address()
    {
        $user = User::factory()->create();

        $this->jsonAs($user, 'POST', 'api/addresses', $payload = [
            'name' => 'John Connor',
            'address_1' => '123 Street',
            'city' => 'USA',
            'postal_code' => '00924',
            // create country and grab the id
            'country_id' => Country::factory()->create()->id
        ]);

        $this->assertDatabaseHas('addresses', array_merge($payload, [
            'user_id' => $user->id
        ]));
    }

    public function test_it_returns_an_address_when_created()
    {
        $user = User::factory()->create();

        $response = $this->jsonAs($user, 'POST', 'api/addresses', $payload = [
            'name' => 'John Connor',
            'address_1' => '123 Street',
            'city' => 'USA',
            'postal_code' => '00924',
            // create country and grab the id
            'country_id' => Country::factory()->create()->id
        ]);

            $response->assertJsonFragment([
               'id' => json_decode($response->getContent())->data->id
            ]);
    }
}
