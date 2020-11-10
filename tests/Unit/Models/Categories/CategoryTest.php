<?php

namespace Tests\Unit\Models\Categories;

use App\Models\Category;
use Tests\TestCase;


class CategoryTest extends TestCase
{
    public function test_it_many_children()
    {
        $category = Category::factory(Category::class)->create();
//        dd($category);

//         Save another category to the child relationship
        $category->children()->save(
            Category::factory()->create()
        );

        // assert that first category we get back is a collection
        $this->assertInstanceOf(Category::class, $category->children->first());
    }

    public function test_it_can_fetch_only_parents()
    {
        $category = Category::factory(Category::class)->create();

        $category->children()->save(
            Category::factory()->create()
        );

        // assert that first category we get back is a collection
        $this->assertEquals(1, Category::parents()->count());
    }

    public function test_it_is_orderable_by_a_numbered_order()
    {
        $category = Category::factory(Category::class)->create([
            'order' => 2
        ]);

        $anotherCategory = Category::factory(Category::class)->create([
            'order' => 1
        ]);

        // assert that first category we get back is a collection
        $this->assertEquals($anotherCategory->name, Category::ordered()->first()->name);
    }
}
