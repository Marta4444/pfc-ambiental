<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario admin principal
        User::factory()->admin()->create([
            'name' => 'Administrador',
            'email' => 'admin@seprona.es',
            'agent_num' => now()->year . '-00001',
        ]);

        // Crear algunos usuarios de prueba
        User::factory(10)->create();

        // Llamar a otros seeders
        $this->call([
            CategorySeeder::class,
            SubcategorySeeder::class,
            FieldSeeder::class,       
            SubcategoryFieldSeeder::class,
            PetitionerSeeder::class,
            SpeciesSeeder::class,           
            ProtectedAreaSeeder::class,     
            ReportSeeder::class,
            ReportDetailSeeder::class,   
            ReportCostItemSeeder::class,    
        ]);
    }
}
