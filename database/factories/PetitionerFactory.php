<?php

namespace Database\Factories;

use App\Models\Petitioner;
use Illuminate\Database\Eloquent\Factories\Factory;

class PetitionerFactory extends Factory
{
    protected $model = Petitioner::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Jueces',
                'Fiscales',
                'EPRONA',
                'Policía Nacional',
                'Guardia Civil',
                'Otro',
            ]),
            'active' => true,
            'order' => $this->faker->numberBetween(1, 999),
        ];
    }
}
