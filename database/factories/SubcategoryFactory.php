<?php

namespace Database\Factories;

use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubcategoryFactory extends Factory
{
    protected $model = Subcategory::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => $this->faker->unique()->randomElement([
                'Comercio',
                'Caza furtiva',
                'Especie Exótica Invasora (EEI)',
                'Endemismos',
                'Especies cinegéticas',
            ]),
            'description' => $this->faker->sentence(8),
            'active' => true,
        ];
    }
}
