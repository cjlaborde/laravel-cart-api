<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
//        dd($this->faker->person);
        return [
//            'name' => $name = 'Sean',
            'name' => $name = $this->faker->unique()->name,
            'slug' => str_slug($name)
        ];
    }
}
