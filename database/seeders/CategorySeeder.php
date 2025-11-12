<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Aguas', 'description' => 'Daños y contaminación de masas de agua (vertidos, eutrofización).', 'base_coeficient' => 1.10],
            ['name' => 'Incendios', 'description' => 'Incendios forestales y urbanos que afectan ecosistemas y personas.', 'base_coeficient' => 1.50],
            ['name' => 'Suelo', 'description' => 'Degradación y contaminación del suelo (erosión, hidrocarburos).', 'base_coeficient' => 1.20],
            ['name' => 'Aire', 'description' => 'Contaminación atmosférica y emisiones tóxicas.', 'base_coeficient' => 1.15],
            ['name' => 'Biodiversidad', 'description' => 'Pérdida de hábitat, especies y efectos sobre ecosistemas.', 'base_coeficient' => 1.40],
            ['name' => 'Residuos', 'description' => 'Gestión inadecuada de residuos y vertederos ilegales.', 'base_coeficient' => 1.25],
        ];

        foreach ($categories as $c) {
            Category::updateOrCreate(
                ['name' => $c['name']],
                ['description' => $c['description'], 'base_coeficient' => $c['base_coeficient'], 'active' => true]
            );
        }
    }
}
