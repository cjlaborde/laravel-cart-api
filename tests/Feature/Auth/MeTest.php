<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MeTest extends TestCase
{
    public function test_it_fails_if_user_isnt_authenticated()
    {
       $this->json('GET', 'api/auth/me')
           ->assertStatus(401);
    }

    public function test_it_returns_user_details()
    {
        // authenticate users
        // and send as header as part of the json
        // grab token
        $user = User::factory()->create();
       $this->jsonAs($user,'GET', 'api/auth/me')
           ->assertJsonFragment([
               'email' => $user->email,
           ]);
    }
}
