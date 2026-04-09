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

        $urgencies = ['normal', 'alta', 'urgente'];
        $communities = ['Andalucía', 'Cataluña', 'Madrid', 'Valencia', 'Galicia', 'Castilla y León'];
        $provinces = ['Sevilla', 'Barcelona', 'Madrid', 'Valencia', 'A Coruña', 'Valladolid'];
        $offices = ['Despacho Central', 'Comandancia Cadiz', 'Oficina Sur', 'Comandancia Barcelona', 'Oficina Oeste'];

        // Grupos de casos con su lógica de estado, asignación, detalles y costes:
        // - nuevo:      no asignado, sin detalles, sin costes
        // - en_proceso: asignado, con detalles parciales, sin costes
        // - en_espera:  asignado, con detalles, sin costes
        // - completado: asignado, con detalles y con costes
        $grupos = [
            ['status' => 'nuevo',       'count' => 3, 'assigned' => false],
            ['status' => 'en_proceso',  'count' => 3, 'assigned' => true],
            ['status' => 'en_espera',   'count' => 2, 'assigned' => true],
            ['status' => 'completado',  'count' => 4, 'assigned' => true],
        ];

        $counter = 1;

        foreach ($grupos as $grupo) {
            for ($i = 1; $i <= $grupo['count']; $i++) {
                $category = $categories->random();
                $subcategory = $category->subcategories()->where('active', true)->inRandomOrder()->first();

                if (!$subcategory) {
                    continue;
                }

                $user = $users->random();
                $assignedTo = $grupo['assigned'] ? $users->random()->id : null;

                Report::create([
                    'user_id'          => $user->id,
                    'category_id'      => $category->id,
                    'subcategory_id'   => $subcategory->id,
                    'ip'               => now()->year . '-IP' . str_pad($counter, 3, '0', STR_PAD_LEFT),
                    'title'            => 'Caso de prueba #' . $counter . ' - ' . $category->name,
                    'background'       => fake()->paragraphs(3, true),
                    'community'        => fake()->randomElement($communities),
                    'province'         => fake()->randomElement($provinces),
                    'locality'         => fake()->city(),
                    'coordinates'      => fake()->latitude() . ',' . fake()->longitude(),
                    'petitioner_id'    => $petitioners->random()->id,
                    'petitioner_other' => null,
                    'office'           => fake()->randomElement($offices),
                    'diligency'        => 'D-' . now()->year . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'urgency'          => fake()->randomElement($urgencies),
                    'date_petition'    => now()->subDays(rand(1, 30)),
                    'date_damage'      => now()->subDays(rand(31, 90)),
                    'status'           => $grupo['status'],
                    'assigned'         => $grupo['assigned'],
                    'assigned_to'      => $assignedTo,
                    'vr_total'         => 0,
                    've_total'         => 0,
                    'vs_total'         => 0,
                    'total_cost'       => 0,
                ]);

                $counter++;
            }
        }

        $this->command->info("Se han creado " . ($counter - 1) . " reportes de prueba coherentes.");
    }
}