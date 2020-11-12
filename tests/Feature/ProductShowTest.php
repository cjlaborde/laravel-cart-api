<?php

namespace Tests\Feature;

use App\Http\Resources\ProductIndexResource;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductShowTest extends TestCase
{
    public function test_it_fails_if_a_product_cant_be_found()
    {
        // look for item that doesn't exist
        $this->json('GET', 'api/products/nope')
            ->assertStatus(404);
    }

    public function test_it_shows_a_product()
    {
        $product =  Product::factory()->create();
        // look for item that doesn't exist
        $this->json('GET', "api/products/{$product->slug}")
            // check if id is in there
            ->assertJsonFragment([
                'id' => $product->id
            ]);
    }
}
