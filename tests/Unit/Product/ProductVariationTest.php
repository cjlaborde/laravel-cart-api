<?php

namespace Tests\Unit\Product;

use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ProductVariationType;
use Tests\TestCase;


class ProductVariationTest extends TestCase
{
    // test App/Models/ProductVariationType has one type
    public function test_it_has_one_variation_type()
    {
        $variation = ProductVariation::factory()->create();

        $this->assertInstanceOf(ProductVariationType::class, $variation->type);
    }

    public function test_it_belongs_to_a_product()
    {
        $variation = ProductVariation::factory()->create();

        $this->assertInstanceOf(Product::class, $variation->product);
    }
}
