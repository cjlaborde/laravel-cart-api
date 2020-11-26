<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tymon\JWTAuth\Contracts\JWTSubject;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    /**
     *  public function jsonAs(user method, endpoint, data)
     * JWTSubject when we authenticate use
     * $method
     * $endpoint we want to hit
     * $data that hwe want to send across that endpoint
     * any headers we want to send across
     */
    // What we are doing is wrapping this method into another method
    // src/Illuminate/Foundation/Testing/Concerns/MakesHttpRequests.php
    public function jsonAs(JWTSubject $user, $method, $endpoint, $data = [], $headers = [])
    {
        // grab token we send through
        $token = auth()->tokenById($user->id);

        // merge header with authorization header
        return $this->json($method, $endpoint, $data, array_merge($headers, [
            'Authorization' => 'Bearer ' . $token
        ]));


    }
}
