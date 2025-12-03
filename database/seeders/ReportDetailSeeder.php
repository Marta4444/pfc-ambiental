<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\ReportDetail;
use App\Models\Species;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class ReportDetailSeeder extends Seeder
{
    /**
     * Datos de ejemplo para cada tipo de subcategoría
     */
    protected array $speciesData = [
        ['name' => 'Lynx pardinus', 'common' => 'Lince ibérico'],
        ['name' => 'Aquila adalberti', 'common' => 'Águila imperial ibérica'],
        ['name' => 'Gypaetus barbatus', 'common' => 'Quebrantahuesos'],
        ['name' => 'Ursus arctos', 'common' => 'Oso pardo'],
        ['name' => 'Canis lupus', 'common' => 'Lobo ibérico'],
        ['name' => 'Otis tarda', 'common' => 'Avutarda'],
        ['name' => 'Ciconia nigra', 'common' => 'Cigüeña negra'],
        ['name' => 'Pterocles alchata', 'common' => 'Ganga ibérica'],
        ['name' => 'Fulica cristata', 'common' => 'Focha moruna'],
        ['name' => 'Marmaronetta angustirostris', 'common' => 'Cerceta pardilla'],
    ];

    protected array $eeiSpecies = [
        ['name' => 'Trachemys scripta', 'common' => 'Galápago de Florida'],
        ['name' => 'Procyon lotor', 'common' => 'Mapache'],
        ['name' => 'Neovison vison', 'common' => 'Visón americano'],
        ['name' => 'Ailanthus altissima', 'common' => 'Árbol del cielo'],
        ['name' => 'Carpobrotus edulis', 'common' => 'Uña de gato'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener reports que tengan subcategorías con campos asignados
        $reports = Report::with('subcategory.fields')->get();

        if ($reports->isEmpty()) {
            $this->command->warn('No hay reportes. Ejecuta primero ReportSeeder.');
            return;
        }

        $reportsWithDetails = 0;
        $totalDetails = 0;

        foreach ($reports as $report) {
            // Solo crear detalles para ~60% de los reports
            if (rand(1, 100) > 60) {
                continue;
            }

            $subcategory = $report->subcategory;
            $fields = $subcategory->fields;

            // Si la subcategoría no tiene campos, saltar
            if ($fields->isEmpty()) {
                continue;
            }

            // Crear entre 1 y 4 grupos de detalles por report
            $numGroups = rand(1, 4);
            $groupPrefix = $this->getGroupPrefix($subcategory->name);

            for ($g = 1; $g <= $numGroups; $g++) {
                $groupKey = "{$groupPrefix}_{$g}";
                $orderIndex = 0;
                $speciesId = null;

                // Generar valores para cada campo
                foreach ($fields as $field) {
                    $value = $this->generateValueForField($field, $subcategory->name, $speciesId);
                    
                    if ($value === null) {
                        continue;
                    }

                    // Si es campo especie, intentar buscar en BD
                    if ($field->key_name === 'especie') {
                        $species = Species::where('scientific_name', $value)
                            ->orWhere('common_name', $value)
                            ->first();
                        $speciesId = $species?->id;
                    }

                    ReportDetail::create([
                        'report_id' => $report->id,
                        'group_key' => $groupKey,
                        'field_key' => $field->key_name,
                        'value' => $value,
                        'species_id' => $speciesId,
                        'protected_area_id' => null,
                        'order_index' => $orderIndex++,
                    ]);

                    $totalDetails++;
                }
            }

            $reportsWithDetails++;
        }

        $this->command->info("Se han creado detalles para {$reportsWithDetails} reportes ({$totalDetails} registros en total).");
    }

    /**
     * Generar valor según el tipo de campo
     */
    protected function generateValueForField($field, string $subcategoryName, ?int &$speciesId): ?string
    {
        // Campos opcionales pueden quedar vacíos a veces
        if (!$field->pivot->is_required && rand(1, 100) > 70) {
            return null;
        }

        return match ($field->key_name) {
            'especie' => $this->getRandomSpecies($subcategoryName),
            'cantidad' => (string) rand(1, 15),
            'madurez' => fake()->randomElement(['Juvenil', 'Subadulto', 'Adulto', 'Senil', 'Desconocido']),
            'estado_vital' => fake()->randomElement(['Vivo', 'Muerto', 'Herido', 'Crítico', 'Desconocido']),
            'metodo_captura' => fake()->randomElement(['Arma de fuego', 'Trampa', 'Lazo', 'Veneno', 'Redes', 'Cepo', 'Otros']),
            'coste_reposicion' => (string) fake()->randomFloat(2, 500, 25000),
            've' => (string) fake()->randomFloat(2, 1000, 50000),
            'coordenadas_afectacion' => fake()->latitude(36, 43) . ', ' . fake()->longitude(-9, 3),
            'volumen' => (string) fake()->randomFloat(2, 0.5, 500),
            'tipo_residuo' => fake()->randomElement(['Sólido urbano', 'Peligroso', 'Industrial', 'Construcción', 'Agrícola', 'Sanitario', 'Otros']),
            'tipo_gas' => fake()->randomElement(['CO2', 'CO', 'NOx', 'SO2', 'CH4', 'Partículas', 'Otros']),
            'superficie_afectada' => (string) fake()->randomFloat(2, 0.1, 50),
            'caudal' => (string) fake()->randomFloat(2, 0.5, 100),
            'origen_agua' => fake()->randomElement(['Superficial', 'Subterránea', 'Pozo', 'Manantial', 'Red pública', 'Otros']),
            'num_aerogeneradores' => (string) rand(1, 50),
            default => null,
        };
    }

    /**
     * Obtener especie aleatoria según subcategoría
     */
    protected function getRandomSpecies(string $subcategoryName): string
    {
        $subcategoryLower = strtolower($subcategoryName);

        // Para EEI usar especies invasoras
        if (str_contains($subcategoryLower, 'exótica') || str_contains($subcategoryLower, 'invasora')) {
            $species = fake()->randomElement($this->eeiSpecies);
        } else {
            $species = fake()->randomElement($this->speciesData);
        }

        // Alternar entre nombre científico y común
        return rand(0, 1) ? $species['name'] : $species['common'];
    }

    /**
     * Obtener prefijo de grupo según subcategoría
     */
    protected function getGroupPrefix(string $subcategoryName): string
    {
        $name = strtolower($subcategoryName);

        if (str_contains($name, 'caza') || str_contains($name, 'especie') || str_contains($name, 'comercio')) {
            return 'species';
        }
        if (str_contains($name, 'vertido') || str_contains($name, 'residuo')) {
            return 'residue';
        }
        if (str_contains($name, 'emisión') || str_contains($name, 'atmosférica')) {
            return 'emission';
        }
        if (str_contains($name, 'agua') || str_contains($name, 'extracción')) {
            return 'water';
        }
        if (str_contains($name, 'eólico') || str_contains($name, 'aerogenerador')) {
            return 'wind';
        }
        if (str_contains($name, 'exótica') || str_contains($name, 'invasora')) {
            return 'eei';
        }

        return 'group';
    }
}