<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Petitioner;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $categories = Category::all();
        $petitioners = Petitioner::where('name', '!=', 'Otro')->get();

        if ($users->isEmpty() || $categories->isEmpty() || $petitioners->isEmpty()) {
            $this->command->warn('No hay suficientes datos para generar reportes. Ejecuta primero los otros seeders.');
            return;
        }

        $statuses = ['nuevo', 'en_proceso', 'en_espera', 'completado'];
        $urgencies = ['normal', 'alta', 'urgente'];
        $communities = ['Andalucía', 'Cataluña', 'Madrid', 'Valencia', 'Galicia', 'Castilla y León'];
        $provinces = ['Sevilla', 'Barcelona', 'Madrid', 'Valencia', 'A Coruña', 'Valladolid'];
        $offices = ['Despacho Central', 'Comandancia Cadiz', 'Oficina Sur', 'Comandancia Barcelona', 'Oficina Oeste'];


        for ($i = 1; $i <= 20; $i++) {
            $category = $categories->random();
            $subcategory = $category->subcategories()->where('active', true)->inRandomOrder()->first();
            
            if (!$subcategory) {
                continue;
            }

            $user = $users->random();
            $assignedTo = rand(0, 1) ? $users->random()->id : null;

            Report::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'subcategory_id' => $subcategory->id,
                'ip' => now()->year . '-IP' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'title' => 'Caso de prueba #' . $i . ' - ' . $category->name,
                'background' => fake()->paragraphs(3, true),
                'community' => fake()->randomElement($communities),
                'province' => fake()->randomElement($provinces),
                'locality' => fake()->city(),
                'coordinates' => fake()->latitude() . ',' . fake()->longitude(),
                'petitioner_id' => $petitioners->random()->id,
                'petitioner_other' => null,
                'office' => fake()->randomElement($offices),
                'diligency' => 'D-' . now()->year . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'urgency' => fake()->randomElement($urgencies),
                'date_petition' => now()->subDays(rand(1, 30)),
                'date_damage' => now()->subDays(rand(31, 90)),
                'status' => fake()->randomElement($statuses),
                'assigned' => !is_null($assignedTo),
                'assigned_to' => $assignedTo,
                'vr_total' => fake()->randomFloat(2, 1000, 50000),
                've_total' => fake()->randomFloat(2, 500, 30000),
                'vs_total' => fake()->randomFloat(2, 200, 15000),
                'total_cost' => fake()->randomFloat(2, 2000, 95000),
            ]);
        }

        $this->command->info('Se han creado 20 reportes de prueba.');
    }
}