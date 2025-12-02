<?php

namespace App\Console\Commands;

use App\Models\Species;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SyncSpeciesData extends Command
{
    protected $signature = 'species:sync 
                            {--source=all : Fuente de datos (boe, gbif, iucn, cites, all)}
                            {--species= : Nombre científico específico para sincronizar}
                            {--taxon= : Grupo taxonómico específico}
                            {--force : Forzar sincronización aunque los datos estén actualizados}
                            {--days=30 : Días desde última sincronización para considerar obsoleto}
                            {--dry-run : Simular sin guardar cambios}';

    protected $description = 'Sincroniza datos de especies desde BOE (CSV), GBIF, IUCN y CITES';

    private int $created = 0;
    private int $updated = 0;
    private int $errors = 0;

    public function handle(): int
    {
        $source = $this->option('source');
        $specificSpecies = $this->option('species');
        $force = $this->option('force');
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("╔════════════════════════════════════════════╗");
        $this->info("║     SINCRONIZACIÓN DE ESPECIES             ║");
        $this->info("╚════════════════════════════════════════════╝");
        $this->newLine();
        
        $this->table(
            ['Parámetro', 'Valor'],
            [
                ['Fuente', $source],
                ['Especie específica', $specificSpecies ?: 'Todas'],
                ['Forzar', $force ? 'Sí' : 'No'],
                ['Días obsolescencia', $days],
                ['Simulación', $dryRun ? 'Sí' : 'No'],
            ]
        );
        $this->newLine();

        try {
            // 1. CARGA INICIAL BOE (CSV)
            if ($source === 'all' || $source === 'boe') {
                $this->syncFromBoe($dryRun);
            }

            // 2. Obtener especies a enriquecer
            $species = $this->getSpeciesToSync($specificSpecies, $force, $days);

            if ($species->isEmpty()) {
                $this->info("✓ No hay especies pendientes de sincronización.");
            } else {
                $this->info("Especies a enriquecer: {$species->count()}");
                $this->newLine();

                $bar = $this->output->createProgressBar($species->count());
                $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% -- %message%');
                $bar->start();

                foreach ($species as $sp) {
                    $bar->setMessage($sp->scientific_name);
                    $this->enrichSpecies($sp, $source, $dryRun);
                    $bar->advance();
                    
                    // Pequeña pausa para no saturar APIs
                    usleep(200000); // 200ms
                }

                $bar->finish();
                $this->newLine(2);
            }

            // Resumen final
            $this->showSummary();

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error fatal: " . $e->getMessage());
            Log::error('Species sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Cargar especies desde el CSV del BOE/MITECO
     */
    protected function syncFromBoe(bool $dryRun): void
    {
        $this->info("📋 Cargando datos del Catálogo Español (BOE)...");

        $csvPath = storage_path('app/data/catalogo_espanol_especies.csv');

        if (!file_exists($csvPath)) {
            $this->warn("   ⚠ Archivo CSV no encontrado: {$csvPath}");
            $this->warn("   Creando archivo de ejemplo...");
            $this->createSampleCsv($csvPath);
        }

        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            $this->error("   ✗ No se pudo abrir el archivo CSV");
            return;
        }

        // Leer cabecera
        $header = fgetcsv($handle);
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) continue;

            $data = array_combine($header, $row);

            if ($dryRun) {
                $this->line("   [DRY-RUN] {$data['scientific_name']}");
                continue;
            }

            $species = Species::updateOrCreate(
                ['scientific_name' => trim($data['scientific_name'])],
                [
                    'common_name' => trim($data['common_name']) ?: null,
                    'taxon_group' => trim($data['taxon_group']) ?: null,
                    'boe_status' => trim($data['boe_status']) ?: null,
                    'boe_law_ref' => trim($data['boe_law_ref']) ?: null,
                    'is_protected' => !empty(trim($data['boe_status'])),
                ]
            );

            if ($species->wasRecentlyCreated) {
                $this->created++;
            } else {
                $this->updated++;
            }
            $count++;
        }

        fclose($handle);
        $this->info("   ✓ Procesadas {$count} especies del BOE");
        $this->newLine();
    }

    /**
     * Crear archivo CSV de ejemplo si no existe
     */
    protected function createSampleCsv(string $path): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $content = <<<CSV
scientific_name,common_name,taxon_group,boe_status,boe_law_ref
Lynx pardinus,Lince ibérico,Mamíferos,En peligro de extinción,Real Decreto 139/2011
Aquila adalberti,Águila imperial ibérica,Aves,En peligro de extinción,Real Decreto 139/2011
Ursus arctos,Oso pardo,Mamíferos,En peligro de extinción,Real Decreto 139/2011
Gypaetus barbatus,Quebrantahuesos,Aves,En peligro de extinción,Real Decreto 139/2011
Monachus monachus,Foca monje del Mediterráneo,Mamíferos,En peligro de extinción,Real Decreto 139/2011
Testudo hermanni,Tortuga mediterránea,Reptiles,En peligro de extinción,Real Decreto 139/2011
Hieraaetus fasciatus,Águila perdicera,Aves,Vulnerable,Real Decreto 139/2011
Otis tarda,Avutarda,Aves,Vulnerable,Real Decreto 139/2011
Ciconia nigra,Cigüeña negra,Aves,Vulnerable,Real Decreto 139/2011
Testudo graeca,Tortuga mora,Reptiles,Vulnerable,Real Decreto 139/2011
Canis lupus signatus,Lobo ibérico,Mamíferos,Régimen de protección especial,Real Decreto 139/2011
Lutra lutra,Nutria,Mamíferos,Régimen de protección especial,Real Decreto 139/2011
Gyps fulvus,Buitre leonado,Aves,Régimen de protección especial,Real Decreto 139/2011
Falco peregrinus,Halcón peregrino,Aves,Régimen de protección especial,Real Decreto 139/2011
Bubo bubo,Búho real,Aves,Régimen de protección especial,Real Decreto 139/2011
CSV;

        file_put_contents($path, $content);
        $this->info("   ✓ Archivo CSV de ejemplo creado");
    }

    /**
     * Obtener especies que necesitan sincronización
     */
    protected function getSpeciesToSync(?string $specificSpecies, bool $force, int $days)
    {
        $query = Species::query();

        if ($specificSpecies) {
            return $query->where('scientific_name', 'LIKE', "%{$specificSpecies}%")->get();
        }

        if (!$force) {
            $query->needsSync($days);
        }

        return $query->orderBy('scientific_name')->get();
    }

    /**
     * Enriquecer una especie con datos de APIs externas
     */
    protected function enrichSpecies(Species $species, string $source, bool $dryRun): void
    {
        $sourceData = $species->source_json ?? [];

        try {
            // GBIF
            if ($source === 'all' || $source === 'gbif') {
                $gbifData = $this->fetchFromGbif($species->scientific_name);
                if ($gbifData) {
                    $sourceData['gbif'] = $gbifData;
                    $sourceData['gbif']['fetched_at'] = now()->toIso8601String();
                    
                    if (!$dryRun) {
                        // Solo actualizar si no tenemos el dato
                        if (empty($species->taxon_group) && !empty($gbifData['taxon_group'])) {
                            $species->taxon_group = $gbifData['taxon_group'];
                        }
                        if (empty($species->common_name) && !empty($gbifData['common_name'])) {
                            $species->common_name = $gbifData['common_name'];
                        }
                    }
                }
            }

            // IUCN
            if ($source === 'all' || $source === 'iucn') {
                $iucnData = $this->fetchFromIucn($species->scientific_name);
                if ($iucnData) {
                    $sourceData['iucn'] = $iucnData;
                    $sourceData['iucn']['fetched_at'] = now()->toIso8601String();
                    
                    if (!$dryRun) {
                        $species->iucn_category = $iucnData['category'] ?? $species->iucn_category;
                        $species->iucn_assessment_year = $iucnData['assessment_year'] ?? $species->iucn_assessment_year;
                    }
                }
            }

            // CITES
            if ($source === 'all' || $source === 'cites') {
                $citesData = $this->fetchFromCites($species->scientific_name);
                if ($citesData) {
                    $sourceData['cites'] = $citesData;
                    $sourceData['cites']['fetched_at'] = now()->toIso8601String();
                    
                    if (!$dryRun) {
                        $species->cites_appendix = $citesData['appendix'] ?? $species->cites_appendix;
                    }
                }
            }

            if (!$dryRun) {
                $species->source_json = $sourceData;
                $species->synced_at = now();
                
                // Recalcular estado de protección
                $species->is_protected = !empty($species->boe_status) 
                    || !empty($species->ccaa_status) 
                    || in_array($species->iucn_category, ['CR', 'EN', 'VU', 'NT'])
                    || !empty($species->cites_appendix);

                $species->save();
                $this->updated++;
            }

        } catch (\Exception $e) {
            $this->errors++;
            Log::warning("Error enriching {$species->scientific_name}: " . $e->getMessage());
        }
    }

    /**
     * GBIF - Global Biodiversity Information Facility
     * API pública sin autenticación
     */
    protected function fetchFromGbif(string $scientificName): ?array
    {
        try {
            $response = Http::timeout(15)
                ->retry(2, 1000)
                ->get('https://api.gbif.org/v1/species/match', [
                    'name' => $scientificName,
                    'strict' => false,
                ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            if (($data['matchType'] ?? '') === 'NONE') {
                return null;
            }

            // Mapear clase taxonómica a grupo
            $taxonGroup = $this->mapTaxonGroup($data);

            // Obtener nombre común en español
            $commonName = null;
            if (isset($data['usageKey'])) {
                $commonName = $this->fetchGbifVernacularName($data['usageKey']);
            }

            return [
                'gbif_key' => $data['usageKey'] ?? null,
                'scientific_name' => $data['scientificName'] ?? $scientificName,
                'canonical_name' => $data['canonicalName'] ?? null,
                'kingdom' => $data['kingdom'] ?? null,
                'phylum' => $data['phylum'] ?? null,
                'class' => $data['class'] ?? null,
                'order' => $data['order'] ?? null,
                'family' => $data['family'] ?? null,
                'genus' => $data['genus'] ?? null,
                'taxon_group' => $taxonGroup,
                'common_name' => $commonName,
                'match_type' => $data['matchType'] ?? null,
                'confidence' => $data['confidence'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::debug("GBIF fetch failed for {$scientificName}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mapear taxonomía GBIF a grupos del sistema
     */
    protected function mapTaxonGroup(array $data): ?string
    {
        $classMap = [
            'Mammalia' => 'Mamíferos',
            'Aves' => 'Aves',
            'Reptilia' => 'Reptiles',
            'Amphibia' => 'Anfibios',
            'Actinopterygii' => 'Peces',
            'Chondrichthyes' => 'Peces',
            'Cephalaspidomorphi' => 'Peces',
            'Insecta' => 'Invertebrados',
            'Arachnida' => 'Invertebrados',
            'Malacostraca' => 'Invertebrados',
            'Gastropoda' => 'Invertebrados',
            'Bivalvia' => 'Invertebrados',
            'Magnoliopsida' => 'Flora',
            'Liliopsida' => 'Flora',
            'Pinopsida' => 'Flora',
            'Polypodiopsida' => 'Flora',
        ];

        if (isset($data['class']) && isset($classMap[$data['class']])) {
            return $classMap[$data['class']];
        }

        if (isset($data['kingdom'])) {
            if ($data['kingdom'] === 'Plantae') return 'Flora';
            if ($data['kingdom'] === 'Fungi') return 'Flora';
            if ($data['kingdom'] === 'Animalia' && isset($data['phylum'])) {
                if (in_array($data['phylum'], ['Arthropoda', 'Mollusca', 'Annelida', 'Cnidaria'])) {
                    return 'Invertebrados';
                }
            }
        }

        return null;
    }

    /**
     * Obtener nombre vernáculo en español desde GBIF
     */
    protected function fetchGbifVernacularName(int $usageKey): ?string
    {
        try {
            $response = Http::timeout(10)
                ->get("https://api.gbif.org/v1/species/{$usageKey}/vernacularNames", [
                    'limit' => 100,
                ]);

            if (!$response->successful()) {
                return null;
            }

            $names = $response->json()['results'] ?? [];
            
            // Prioridad: español > inglés > cualquiera
            $priorities = ['es', 'spa', 'en', 'eng'];
            
            foreach ($priorities as $lang) {
                foreach ($names as $name) {
                    $nameLang = strtolower($name['language'] ?? '');
                    if ($nameLang === $lang) {
                        return $name['vernacularName'];
                    }
                }
            }

            // Si no hay idioma preferido, devolver el primero
            return $names[0]['vernacularName'] ?? null;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * IUCN Red List API
     * Requiere token de https://apiv3.iucnredlist.org/
     */
    protected function fetchFromIucn(string $scientificName): ?array
    {
        $token = env('IUCN_API_TOKEN', '');
        
        if (empty($token)) {
            // Solo mostrar aviso una vez
            static $warned = false;
            if (!$warned) {
                $this->warn("   ⚠ IUCN_API_TOKEN no configurado. Omitiendo IUCN.");
                $warned = true;
            }
            return null;
        }

        try {
            $encodedName = urlencode($scientificName);
            $response = Http::timeout(15)
                ->retry(2, 1000)
                ->get("https://apiv3.iucnredlist.org/api/v3/species/{$encodedName}", [
                    'token' => $token,
                ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();
            $result = $data['result'][0] ?? null;

            if (!$result) {
                return null;
            }

            return [
                'taxonid' => $result['taxonid'] ?? null,
                'scientific_name' => $result['scientific_name'] ?? null,
                'category' => $result['category'] ?? null,
                'assessment_year' => isset($result['assessment_date']) 
                    ? (int) substr($result['assessment_date'], 0, 4) 
                    : null,
                'population_trend' => $result['population_trend'] ?? null,
                'main_common_name' => $result['main_common_name'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::debug("IUCN fetch failed for {$scientificName}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * CITES Species+ API
     * Requiere token de https://api.speciesplus.net/
     */
    protected function fetchFromCites(string $scientificName): ?array
    {
        $token = env('CITES_API_TOKEN', '');
        
        if (empty($token)) {
            static $warnedCites = false;
            if (!$warnedCites) {
                $this->warn("   ⚠ CITES_API_TOKEN no configurado. Omitiendo CITES.");
                $warnedCites = true;
            }
            return null;
        }

        try {
            $response = Http::timeout(15)
                ->retry(2, 1000)
                ->withHeaders([
                    'X-Authentication-Token' => $token,
                ])
                ->get('https://api.speciesplus.net/api/v1/taxon_concepts', [
                    'name' => $scientificName,
                ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();
            $taxon = $data['taxon_concepts'][0] ?? null;

            if (!$taxon) {
                return null;
            }

            // Buscar listado CITES actual
            $citesAppendix = null;
            foreach ($taxon['cites_listings'] ?? [] as $listing) {
                if ($listing['is_current'] ?? false) {
                    $citesAppendix = $listing['appendix'] ?? null;
                    break;
                }
            }

            return [
                'taxon_id' => $taxon['id'] ?? null,
                'full_name' => $taxon['full_name'] ?? null,
                'author_year' => $taxon['author_year'] ?? null,
                'appendix' => $citesAppendix,
                'cites_listing_count' => count($taxon['cites_listings'] ?? []),
            ];

        } catch (\Exception $e) {
            Log::debug("CITES fetch failed for {$scientificName}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mostrar resumen de la sincronización
     */
    protected function showSummary(): void
    {
        $this->newLine();
        $this->info("╔════════════════════════════════════════════╗");
        $this->info("║              RESUMEN                       ║");
        $this->info("╚════════════════════════════════════════════╝");
        
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Especies creadas', $this->created],
                ['Especies actualizadas', $this->updated],
                ['Errores', $this->errors],
                ['Total en BD', Species::count()],
                ['Protegidas', Species::where('is_protected', true)->count()],
            ]
        );
    }
}
