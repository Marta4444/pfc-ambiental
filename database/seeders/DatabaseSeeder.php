<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario admin principal (sin factory para evitar dependencia de Faker en producción)
        User::firstOrCreate(
            ['email' => 'admin@seprona.es'],
            [
                'name'       => 'Administrador',
                'password'   => Hash::make('admin1234'),
                'role'       => 'admin',
                'agent_num'  => now()->year . '-00001',
                'active'     => true,
            ]
        );

        // Llamar a seeders de datos reales (sin dependencia de Faker)
        $this->call([
            CategorySeeder::class,
            SubcategorySeeder::class,
            FieldSeeder::class,
            SubcategoryFieldSeeder::class,            ExtraccionAguasFieldSeeder::class,
            VertidoAguasFieldSeeder::class,            PetitionerSeeder::class,
            // SpeciesSeeder::class -> importación desde API
            ProtectedAreaSeeder::class,
            // ReportSeeder, ReportDetailSeeder, ReportCostItemSeeder -> datos de prueba, no para producción
        ]);
    }
}
