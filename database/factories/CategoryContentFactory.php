<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\CategoryContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CategoryContent>
 */
class CategoryContentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'price' => fake()->randomElement(['от 50 TMT', 'от 100 TMT', null]),
        ];
    }
}
