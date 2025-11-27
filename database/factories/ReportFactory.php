<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Report;
use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Petitioner;

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
        $category = Category::where('active', true)->inRandomOrder()->first();
        $subcategory = $category ? $category->subcategories()->where('active', true)->inRandomOrder()->first() : null;
        $petitioner = Petitioner::where('active', true)->inRandomOrder()->first();
        $user = User::inRandomOrder()->first();
        $assignedTo = rand(0, 1) ? User::inRandomOrder()->first()?->id : null;

        return [
            'user_id' => $user?->id ?? User::factory(),
            'category_id' => $category?->id ?? Category::factory(),
            'subcategory_id' => $subcategory?->id ?? Subcategory::factory(),
            'ip' => now()->year . '-IP' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'title' => $this->faker->sentence(4),
            'background' => $this->faker->paragraphs(3, true),
            'community' => $this->faker->randomElement(['Andalucía', 'Cataluña', 'Madrid', 'Valencia']),
            'province' => $this->faker->randomElement(['Sevilla', 'Barcelona', 'Madrid', 'Valencia']),
            'locality' => $this->faker->city(),
            'location' => $this->faker->streetAddress(),
            'coordinates' => $this->faker->latitude() . ',' . $this->faker->longitude(),
            'petitioner_id' => $petitioner?->id ?? Petitioner::factory(),
            'petitioner_other' => null,
            'office' => $this->faker->randomElement(['Despacho Central', 'Oficina Norte', 'Oficina Sur', 'Oficina Este']),
            'diligency' => 'D-' . now()->year . '-' . str_pad($this->faker->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'urgency' => $this->faker->randomElement(['normal', 'alta', 'urgente']),
            'date_petition' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'date_damage' => $this->faker->dateTimeBetween('-90 days', '-31 days'),
            'status' => $this->faker->randomElement(['nuevo', 'en_proceso', 'en_espera', 'completado']),
            'assigned' => !is_null($assignedTo),
            'assigned_to' => $assignedTo,
            'pdf_report' => null,
            'vr_total' => $this->faker->randomFloat(2, 1000, 50000),
            've_total' => $this->faker->randomFloat(2, 500, 30000),
            'vs_total' => $this->faker->randomFloat(2, 200, 15000),
            'total_cost' => $this->faker->randomFloat(2, 2000, 95000),
        ];
    }
}
