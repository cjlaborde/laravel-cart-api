<?php

namespace Tests\Feature\Products;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductScopingTest extends TestCase
{
    public function test_it_can_scope_by_category()
    {
        // create product then create another product that is not attached to that category
        // then when we check request we want to make sure count of product inside of that is only 1 result
        $product =  Product::factory()->create();

        $product->categories()->save(
            $category = Category::factory()->create()
        );

        $anotherProduct =  Product::factory()->create();

        $this->json('GET', "api/products?category={$category->slug}")
             ->assertJsonCount(1, 'data');
        // you can make test fail by commenting 'category' => new CategoryScope() in ProductController
    }
}
