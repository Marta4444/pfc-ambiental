<?php

namespace Database\Factories;

use App\Models\Field;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Field>
 */
class FieldFactory extends Factory
{
    protected $model = Field::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(Field::VALID_TYPES);
        $isNumeric = in_array($type, ['number', 'decimal']);
        
        $optionsJson = null;
        if (in_array($type, ['select', 'multiselect', 'radio'])) {
            $optionsJson = $this->faker->randomElements(
                ['Opción A', 'Opción B', 'Opción C', 'Opción D', 'Opción E'],
                $this->faker->numberBetween(2, 5)
            );
        }

        return [
            'key_name' => $this->faker->unique()->slug(2),
            'label' => $this->faker->words(3, true),
            'type' => $type,
            'units' => $isNumeric ? $this->faker->randomElement(['€', 'kg', 'm', 'l', 'ha', 'm²', 'm³', null]) : null,
            'options_json' => $optionsJson,
            'help_text' => $this->faker->optional(0.7)->sentence(),
            'placeholder' => $this->faker->optional(0.5)->words(2, true),
            'is_numeric' => $isNumeric,
            'active' => $this->faker->boolean(90), // 90% activos
        ];
    }

    /**
     * Indicar que el campo es numérico
     */
    public function numeric(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $this->faker->randomElement(['number', 'decimal']),
            'is_numeric' => true,
            'units' => $this->faker->randomElement(['€', 'kg', 'm', 'l', 'ha', 'm²', 'm³']),
        ]);
    }

    /**
     * Indicar que el campo es de tipo select
     */
    public function select(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'select',
            'is_numeric' => false,
            'options_json' => $this->faker->randomElements(
                ['Opción A', 'Opción B', 'Opción C', 'Opción D'],
                $this->faker->numberBetween(2, 4)
            ),
        ]);
    }

    /**
     * Indicar que el campo está activo
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
        ]);
    }

    /**
     * Indicar que el campo está inactivo
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
