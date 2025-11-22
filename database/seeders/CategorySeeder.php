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
            
            
            ['name' => 'Biodiversidad', 'description' => 'Diferentes tipologías de hechos ilícitos, a nivel nacionel e internacional, relacionados con la fauna y la flora, que ongloben especies internacionales, nacionales, endémicas y cinegéticas'],
            ['name' => 'Infraestructuras', 'description' => 'Hechos ilícitos que engloben la afección a diferentes recursos naturales mediante el uso de diferentes tipos de infraestructuras'],
            ['name' => 'Vertidos', 'description' => 'Hechos ilícitos relacionados con el vertido de residuos o efluentes en diferentes matrices como aguas o suelos'],
        ];

        foreach ($categories as $c) {
            Category::updateOrCreate(
                ['name' => $c['name']],
                ['description' => $c['description'], 'active' => true]
            );
        }
    }
}
