<?php

namespace Database\Seeders;

use App\Models\Petitioner;
use Illuminate\Database\Seeder;

class PetitionerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $petitioners = [
            ['name' => 'Jueces', 'active' => true, 'order' => 1],
            ['name' => 'Fiscales', 'active' => true, 'order' => 2],
            ['name' => 'EPRONA', 'active' => true, 'order' => 3],
            ['name' => 'Policía Nacional', 'active' => true, 'order' => 4],
            ['name' => 'Guardia Civil', 'active' => true, 'order' => 5],
            ['name' => 'Otro', 'active' => true, 'order' => 999],
        ];

        foreach ($petitioners as $petitioner) {
            Petitioner::create($petitioner);
        }
    }
}