<?php

namespace App\Console\Commands;

use App\Models\ProtectedArea;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncProtectedAreas extends Command
{
    protected $signature = 'protected-areas:sync 
                            {--source=wdpa : Fuente de datos (wdpa, csv)}
                            {--region= : Filtrar por región/CCAA}
                            {--type= : Filtrar por tipo de protección}
                            {--force : Forzar actualización de todas las áreas}
                            {--dry-run : Simular sin guardar cambios}';

    protected $description = 'Sincroniza áreas protegidas desde APIs externas (WDPA/Protected Planet)';

    private int $created = 0;
    private int $updated = 0;
    private int $errors = 0;

    // Token de Protected Planet (obtener en https://api.protectedplanet.net/)
    // Por ahora usamos el endpoint público que no requiere token para búsquedas básicas

    public function handle(): int
    {
        $source = $this->option('source');
        $region = $this->option('region');
        $type = $this->option('type');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        $this->info("╔════════════════════════════════════════════╗");
        $this->info("║   SINCRONIZACIÓN DE ÁREAS PROTEGIDAS       ║");
        $this->info("╚════════════════════════════════════════════╝");
        $this->newLine();

        $this->table(['Parámetro', 'Valor'], [
            ['Fuente', $source],
            ['Región', $region ?: 'Todas'],
            ['Tipo', $type ?: 'Todos'],
            ['Forzar', $force ? 'Sí' : 'No'],
            ['Simulación', $dryRun ? 'Sí' : 'No'],
        ]);
        $this->newLine();

        try {
            if ($source === 'wdpa') {
                $this->syncFromWdpa($region, $dryRun);
            } elseif ($source === 'csv') {
                $this->syncFromCsv($dryRun);
            }

            $this->showSummary();
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error('Protected areas sync failed', ['error' => $e->getMessage()]);
            return Command::FAILURE;
        }
    }

    /**
     * Sincronizar desde Protected Planet API (WDPA)
     */
    protected function syncFromWdpa(?string $region, bool $dryRun): void
    {
        $this->info("📍 Obteniendo áreas protegidas de España desde WDPA...");

        // La API de Protected Planet requiere token para datos completos
        // Usamos el endpoint de búsqueda por país
        $token = env('WDPA_API_TOKEN', '');

        if (empty($token)) {
            $this->warn("⚠ WDPA_API_TOKEN no configurado.");
            $this->warn("  Registra tu token en: https://api.protectedplanet.net/");
            $this->warn("  Usando datos de ejemplo en su lugar...");
            $this->loadSampleData($dryRun);
            return;
        }

        $page = 1;
        $perPage = 50;
        $hasMore = true;

        while ($hasMore) {
            $this->info("  Obteniendo página {$page}...");

            $response = Http::timeout(30)
                ->withToken($token)
                ->get('https://api.protectedplanet.net/v3/protected_areas', [
                    'country' => 'ESP', // España
                    'per_page' => $perPage,
                    'page' => $page,
                    'with_geometry' => 'true',
                ]);

            if (!$response->successful()) {
                $this->error("  Error en API: " . $response->status());
                break;
            }

            $data = $response->json();
            $areas = $data['protected_areas'] ?? [];

            if (empty($areas)) {
                $hasMore = false;
                break;
            }

            $bar = $this->output->createProgressBar(count($areas));
            $bar->start();

            foreach ($areas as $areaData) {
                $this->processWdpaArea($areaData, $dryRun);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            $page++;
            $hasMore = count($areas) === $perPage;

            // Pausa para no saturar la API
            sleep(1);
        }
    }

    /**
     * Procesar un área de WDPA
     */
    protected function processWdpaArea(array $data, bool $dryRun): void
    {
        try {
            $wdpaId = $data['wdpa_id'] ?? $data['id'] ?? null;
            $name = $data['name'] ?? 'Sin nombre';

            // Extraer bounding box de la geometría
            $geometry = $data['geojson'] ?? $data['geometry'] ?? null;
            $bbox = $this->extractBoundingBox($geometry);

            // Mapear tipo de protección
            $protectionType = $this->mapDesignationType($data['designation'] ?? $data['iucn_category'] ?? '');

            $areaData = [
                'name' => $name,
                'wdpa_id' => $wdpaId,
                'protection_type' => $protectionType,
                'iucn_category' => $data['iucn_category']['code'] ?? $data['iucn_category'] ?? null,
                'designation' => $data['designation'] ?? null,
                'lat_min' => $bbox['lat_min'],
                'lat_max' => $bbox['lat_max'],
                'long_min' => $bbox['long_min'],
                'long_max' => $bbox['long_max'],
                'geometry' => $geometry,
                'description' => $data['original_name'] ?? null,
                'area_km2' => $data['reported_area'] ?? null,
                'region' => $data['sub_locations'][0]['english_name'] ?? null,
                'established_year' => $data['legal_status_updated_at'] 
                    ? (int) substr($data['legal_status_updated_at'], 0, 4) 
                    : null,
                'source' => 'WDPA',
                'synced_at' => now(),
                'source_json' => $data,
                'active' => true,
            ];

            if ($dryRun) {
                $this->line("  [DRY-RUN] {$name}");
                return;
            }

            $area = ProtectedArea::updateOrCreate(
                ['wdpa_id' => $wdpaId],
                $areaData
            );

            if ($area->wasRecentlyCreated) {
                $this->created++;
            } else {
                $this->updated++;
            }

        } catch (\Exception $e) {
            $this->errors++;
            Log::warning("Error procesando área: " . $e->getMessage());
        }
    }

    /**
     * Extraer bounding box de geometría GeoJSON
     */
    protected function extractBoundingBox(?array $geometry): array
    {
        $bbox = [
            'lat_min' => null,
            'lat_max' => null,
            'long_min' => null,
            'long_max' => null,
        ];

        if (empty($geometry)) {
            return $bbox;
        }

        $coordinates = $this->extractAllCoordinates($geometry);

        if (empty($coordinates)) {
            return $bbox;
        }

        $lats = array_column($coordinates, 1);
        $longs = array_column($coordinates, 0);

        return [
            'lat_min' => min($lats),
            'lat_max' => max($lats),
            'long_min' => min($longs),
            'long_max' => max($longs),
        ];
    }

    /**
     * Extraer todas las coordenadas de una geometría
     */
    protected function extractAllCoordinates(array $geometry): array
    {
        $type = $geometry['type'] ?? '';
        $coords = $geometry['coordinates'] ?? [];
        $allCoords = [];

        if ($type === 'Point') {
            $allCoords[] = $coords;
        } elseif ($type === 'Polygon') {
            foreach ($coords as $ring) {
                $allCoords = array_merge($allCoords, $ring);
            }
        } elseif ($type === 'MultiPolygon') {
            foreach ($coords as $polygon) {
                foreach ($polygon as $ring) {
                    $allCoords = array_merge($allCoords, $ring);
                }
            }
        }

        return $allCoords;
    }

    /**
     * Mapear tipo de designación a nuestros tipos
     */
    protected function mapDesignationType(?string $designation): string
    {
        if (empty($designation)) {
            return 'Otro';
        }

        $designationLower = strtolower($designation);

        $mapping = [
            'national park' => 'Parque Nacional',
            'parque nacional' => 'Parque Nacional',
            'natural park' => 'Parque Natural',
            'parque natural' => 'Parque Natural',
            'nature reserve' => 'Reserva Natural',
            'reserva natural' => 'Reserva Natural',
            'biosphere reserve' => 'Reserva de la Biosfera',
            'reserva de la biosfera' => 'Reserva de la Biosfera',
            'natural monument' => 'Monumento Natural',
            'monumento natural' => 'Monumento Natural',
            'protected landscape' => 'Paisaje Protegido',
            'paisaje protegido' => 'Paisaje Protegido',
            'spa' => 'ZEPA',
            'zepa' => 'ZEPA',
            'special protection area' => 'ZEPA',
            'sci' => 'LIC',
            'lic' => 'LIC',
            'site of community importance' => 'LIC',
            'sac' => 'ZEC',
            'zec' => 'ZEC',
            'special area of conservation' => 'ZEC',
            'ramsar' => 'Humedal Ramsar',
            'marine' => 'Área Marina Protegida',
        ];

        foreach ($mapping as $key => $value) {
            if (str_contains($designationLower, $key)) {
                return $value;
            }
        }

        return 'Espacio Natural Protegido';
    }

    /**
     * Cargar datos de ejemplo cuando no hay API token
     */
    protected function loadSampleData(bool $dryRun): void
    {
        $this->info("  Cargando datos de ejemplo...");

        $sampleAreas = [
            [
                'name' => 'Parque Nacional de Doñana',
                'wdpa_id' => 'ES0000024',
                'protection_type' => 'Parque Nacional',
                'iucn_category' => 'II',
                'lat_min' => 36.7833,
                'lat_max' => 37.1333,
                'long_min' => -6.5667,
                'long_max' => -6.1500,
                'area_km2' => 543.00,
                'region' => 'Andalucía',
                'established_year' => 1969,
            ],
            [
                'name' => 'Parque Nacional de Ordesa y Monte Perdido',
                'wdpa_id' => 'ES0000016',
                'protection_type' => 'Parque Nacional',
                'iucn_category' => 'II',
                'lat_min' => 42.5833,
                'lat_max' => 42.7500,
                'long_min' => -0.1167,
                'long_max' => 0.0833,
                'area_km2' => 156.00,
                'region' => 'Aragón',
                'established_year' => 1918,
            ],
            [
                'name' => 'Parque Nacional de Picos de Europa',
                'wdpa_id' => 'ES0000003',
                'protection_type' => 'Parque Nacional',
                'iucn_category' => 'II',
                'lat_min' => 43.1333,
                'lat_max' => 43.2833,
                'long_min' => -5.0000,
                'long_max' => -4.6167,
                'area_km2' => 647.00,
                'region' => 'Asturias',
                'established_year' => 1995,
            ],
            [
                'name' => 'Parque Nacional de Sierra Nevada',
                'wdpa_id' => 'ES0000031',
                'protection_type' => 'Parque Nacional',
                'iucn_category' => 'II',
                'lat_min' => 36.9167,
                'lat_max' => 37.2000,
                'long_min' => -3.4833,
                'long_max' => -2.8500,
                'area_km2' => 862.00,
                'region' => 'Andalucía',
                'established_year' => 1999,
            ],
            [
                'name' => 'Parque Nacional del Teide',
                'wdpa_id' => 'ES0000043',
                'protection_type' => 'Parque Nacional',
                'iucn_category' => 'II',
                'lat_min' => 28.2000,
                'lat_max' => 28.3333,
                'long_min' => -16.6500,
                'long_max' => -16.4667,
                'area_km2' => 189.90,
                'region' => 'Canarias',
                'established_year' => 1954,
            ],
            [
                'name' => 'Parque Nacional de Cabañeros',
                'wdpa_id' => 'ES0000091',
                'protection_type' => 'Parque Nacional',
                'iucn_category' => 'II',
                'lat_min' => 39.3667,
                'lat_max' => 39.5500,
                'long_min' => -4.5667,
                'long_max' => -4.2167,
                'area_km2' => 408.56,
                'region' => 'Castilla-La Mancha',
                'established_year' => 1995,
            ],
            [
                'name' => 'ZEPA Marismas del Guadalquivir',
                'wdpa_id' => 'ES0000272',
                'protection_type' => 'ZEPA',
                'iucn_category' => 'IV',
                'lat_min' => 36.8000,
                'lat_max' => 37.1000,
                'long_min' => -6.4000,
                'long_max' => -6.0000,
                'area_km2' => 137.00,
                'region' => 'Andalucía',
                'established_year' => 1987,
            ],
            [
                'name' => 'LIC Delta del Ebro',
                'wdpa_id' => 'ES5140001',
                'protection_type' => 'LIC',
                'iucn_category' => 'V',
                'lat_min' => 40.5500,
                'lat_max' => 40.8000,
                'long_min' => 0.6000,
                'long_max' => 0.9500,
                'area_km2' => 490.00,
                'region' => 'Cataluña',
                'established_year' => 1997,
            ],
            [
                'name' => 'Parque Natural de la Albufera',
                'wdpa_id' => 'ES0000023',
                'protection_type' => 'Parque Natural',
                'iucn_category' => 'V',
                'lat_min' => 39.2833,
                'lat_max' => 39.4333,
                'long_min' => -0.4000,
                'long_max' => -0.2667,
                'area_km2' => 211.20,
                'region' => 'Comunidad Valenciana',
                'established_year' => 1986,
            ],
            [
                'name' => 'Reserva Natural Laguna de Gallocanta',
                'wdpa_id' => 'ES2430046',
                'protection_type' => 'Reserva Natural',
                'iucn_category' => 'IV',
                'lat_min' => 40.9500,
                'lat_max' => 41.0500,
                'long_min' => -1.5500,
                'long_max' => -1.4500,
                'area_km2' => 19.24,
                'region' => 'Aragón',
                'established_year' => 2006,
            ],
        ];

        $bar = $this->output->createProgressBar(count($sampleAreas));
        $bar->start();

        foreach ($sampleAreas as $areaData) {
            if ($dryRun) {
                $this->line("  [DRY-RUN] {$areaData['name']}");
            } else {
                $area = ProtectedArea::updateOrCreate(
                    ['wdpa_id' => $areaData['wdpa_id']],
                    array_merge($areaData, [
                        'source' => 'sample',
                        'synced_at' => now(),
                        'active' => true,
                    ])
                );

                if ($area->wasRecentlyCreated) {
                    $this->created++;
                } else {
                    $this->updated++;
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Sincronizar desde CSV local
     */
    protected function syncFromCsv(bool $dryRun): void
    {
        $csvPath = storage_path('app/data/protected_areas.csv');

        if (!file_exists($csvPath)) {
            $this->error("Archivo CSV no encontrado: {$csvPath}");
            return;
        }

        $this->info("📍 Cargando áreas protegidas desde CSV...");

        // Implementar similar a species:sync --source=boe
        // Por ahora, placeholder
        $this->warn("  Importación desde CSV pendiente de implementar.");
    }

    /**
     * Mostrar resumen
     */
    protected function showSummary(): void
    {
        $this->newLine();
        $this->info("╔════════════════════════════════════════════╗");
        $this->info("║              RESUMEN                       ║");
        $this->info("╚════════════════════════════════════════════╝");

        $this->table(['Métrica', 'Cantidad'], [
            ['Áreas creadas', $this->created],
            ['Áreas actualizadas', $this->updated],
            ['Errores', $this->errors],
            ['Total en BD', ProtectedArea::count()],
        ]);
    }
}