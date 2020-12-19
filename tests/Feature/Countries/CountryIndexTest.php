<?php

namespace Tests\Feature\Countries;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CountryIndexTest extends TestCase
{
    public function test_it_returns_countries()
    {
       $country = Country::factory()->create();

       $this->json('GET', 'api/countries')
            ->assertJsonFragment([
                'id' => $country->id
            ]);
    }
}
