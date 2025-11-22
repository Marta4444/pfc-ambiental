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
            'Biodiversidad' => [
                [
                    'name' => 'Comercio',
                    'description' => 'Hechos ilícitos relacionados con tráfico no autorizado de animales, plantas o sus derivados, que pone en riesgo la conservación de especies y la biodiversidad.',
                    'coeficient' => 1.50
                ],
                [
                    'name' => 'Caza furtiva',
                    'description' => 'Hechos ilícitos relacionados con la captura o matanza ilegal de animales silvestres, realizada sin permisos o fuera de la normativa, que amenaza la conservación de las especies.',
                    'coeficient' => 1.45
                ],
                [
                    'name' => 'Especie Exótica Invasora (EEI)',
                    'description' => 'Hechos ilícitos relacionados con la introducción o expansión de especies no autóctonas que alteran los ecosistemas y desplazan a la biodiversidad nativa.',
                    'coeficient' => 1.45
                ],
                [
                    'name' => 'Endemismos',
                    'description' => 'Hechos ilícitos relacionados con la afectación o pérdida de especies únicas de un área concreta, cuya alteración compromete la biodiversidad local.',
                    'coeficient' => 1.45
                ],
                [
                    'name' => 'Especies cinegéticas',
                    'description' => 'Hechos ilícitos relacionados con impactos provocados sobre especies usadas para la caza, afectando a su conservación y el equilibrio de las poblaciones silvestres.',
                    'coeficient' => 1.45
                ],
            ],
            'Infraestructuras' => [
                [
                    'name' => 'Extracciones de aguas',
                    'description' => 'Hechos ilícitos relacionados con la captación o el uso excesivo de recursos hídricos que alteran el caudal natural y afectan a los ecosistemas dependientes del agua.',
                    'coeficient' => 1.25
                ],
                [
                    'name' => 'Parques eólicos',
                    'description' => 'Hechos ilícitos relacionados con impactos derivados de instalaciones eólicas que pueden afectar a aves, murciélagos y al equilibrio del entorno natural.',
                    'coeficient' => 1.20
                ],
            ],
            'Vertidos' => [
                [
                    'name' => 'Vertido de residuos',
                    'description' => 'Hechos ilícitos relacionados con la descarga o depósito inadecuado de residuos que contamina el suelo, el agua o el entorno natural.'
                ],
                [
                    'name' => 'Vertido de aguas',
                    'description' => 'Hechos ilícitos relacionados con la liberación de aguas residuales o contaminadas que deteriora la calidad del agua y los ecosistemas acuáticos.'
                ],
                [
                    'name' => 'Vertido de suelos',
                    'description' => 'Hechos ilícitos relacionados con el depósito inadecuado de materiales o sustancias en el suelo, que provoca contaminación y degradación del terreno.'
                ],
                [
                    'name' => 'Emisiones atmosféricas',
                    'description' => 'Hechos ilícitos relacionados con la liberación de gases o partículas contaminantes al aire, que afectan a la calidad del aire y los ecosistemas.'
                ],
                
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
                        'description' => $s['description'],
                        'active' => true,
                    ]
                );
            }
        }
    }
}
