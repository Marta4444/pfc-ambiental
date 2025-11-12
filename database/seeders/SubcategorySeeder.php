<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Subcategory;

class SubcategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $map = [
            'Aguas' => [
                ['name' => 'Vertidos industriales', 'coeficient' => 1.30],
                ['name' => 'Contaminación por agroquímicos', 'coeficient' => 1.20],
                ['name' => 'Eutrofización', 'coeficient' => 1.10],
            ],
            'Incendios' => [
                ['name' => 'Forestales', 'coeficient' => 1.60],
                ['name' => 'Urbanos', 'coeficient' => 1.40],
            ],
            'Suelo' => [
                ['name' => 'Erosión', 'coeficient' => 1.10],
                ['name' => 'Contaminación por hidrocarburos', 'coeficient' => 1.35],
            ],
            'Aire' => [
                ['name' => 'Emisiones industriales', 'coeficient' => 1.25],
                ['name' => 'Quema abierta', 'coeficient' => 1.20],
            ],
            'Biodiversidad' => [
                ['name' => 'Pérdida de hábitat', 'coeficient' => 1.50],
                ['name' => 'Caza furtiva', 'coeficient' => 1.45],
            ],
            'Residuos' => [
                ['name' => 'Vertederos ilegales', 'coeficient' => 1.30],
                ['name' => 'Residuos peligrosos', 'coeficient' => 1.40],
            ],
        ];

        foreach ($map as $categoryName => $subs) {
            $category = Category::where('name', $categoryName)->first();
            if (! $category) continue;
            foreach ($subs as $s) {
                Subcategory::updateOrCreate(
                    ['name' => $s['name']],
                    [
                        'category_id' => $category->id,
                        'description' => $s['name'],
                        'coeficient' => $s['coeficient'],
                        'active' => true,
                    ]
                );
            }
        }
    }
}
