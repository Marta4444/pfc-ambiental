<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Report;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'location' => $this->faker->city(),
            'coordinates' => $this->faker->latitude() . ',' . $this->faker->longitude(),
            'date_damage' => $this->faker->date(),
            'affected_area' => $this->faker->randomFloat(2, 0.1, 5000),
            'criticallity' => $this->faker->numberBetween(1, 5),
            'status' => $this->faker->randomElement(['pendiente', 'procesando', 'resuelto']),
            'pdf_report' => null,
        ];
    }
}
