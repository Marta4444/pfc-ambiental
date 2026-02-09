<?php

namespace App\Services;

use App\Models\Species;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Servicio para sincronizar especies desde APIs externas
 * 
 * APIs integradas:
 * - GBIF (Global Biodiversity Information Facility) - Datos taxonómicos base, gratuita y sin límite
 * - IUCN Red List - Estado de conservación (requiere token)
 * - CITES Species+ - Apéndices de comercio (requiere token)
 * 
 * IMPORTANTE: Los siguientes campos NUNCA se sobrescriben con la sincronización:
 * - boe_status: Estado BOE (añadido manualmente)
 * - boe_law_ref: Referencia legal BOE
 * - ccaa_status: Protección por Comunidad Autónoma
 * - base_value: Valor económico asignado
 * - manually_added: Flag de especie añadida manualmente
 * 
 * Esto permite que los usuarios enriquezcan los datos y la sincronización
 * solo actualice la información de las APIs sin borrar datos locales.
 */
class SpeciesSyncService
{
    /**
     * URLs base de las APIs (configurables en services.php)
     */
    private string $gbifApiUrl;
    private string $iucnApiUrl;
    private string $citesApiUrl;

    /**
     * Tokens de API (configurar en .env)
     */
    private ?string $iucnToken;
    private ?string $citesToken;

    /**
     * Campos que NUNCA se sobrescriben durante la sincronización
     * (datos introducidos manualmente por usuarios)
     */
    private const PROTECTED_FIELDS = [
        'boe_status',
        'boe_law_ref', 
        'ccaa_status',
        'base_value',
        'manually_added',
    ];

    /**
     * Estadísticas de sincronización
     */
    private array $stats = [
        'processed' => 0,
        'created' => 0,
        'updated' => 0,
        'errors' => 0,
        'skipped' => 0,
    ];

    public function __construct()
    {
        $this->gbifApiUrl = config('services.gbif.base_url', 'https://api.gbif.org/v1');
        $this->iucnApiUrl = config('services.iucn.base_url', 'https://apiv3.iucnredlist.org/api/v3');
        $this->citesApiUrl = config('services.cites.base_url', 'https://api.speciesplus.net/api/v1');
        $this->iucnToken = config('services.iucn.token');
        $this->citesToken = config('services.cites.token');
    }

    /**
     * Sincronizar todas las especies pendientes
     * 
     * @param int $limit Número máximo de especies a procesar. 0 = sin límite (todas)
     * @param bool $forceResync Forzar resincronización incluso si ya está actualizada
     */
    public function syncAll(int $limit = 100, bool $forceResync = false): array
    {
        $this->resetStats();

        $query = Species::query();
        
        if (!$forceResync) {
            $query->where(function ($q) {
                $q->where('sync_status', 'pending')
                  ->orWhere('sync_status', 'error')
                  ->orWhereNull('synced_at')
                  ->orWhere('synced_at', '<', now()->subDays(30));
            });
        }

        // Si limit > 0, aplicar límite. Si es 0, procesar todas
        if ($limit > 0) {
            $query->limit($limit);
        }

        $species = $query->get();

        foreach ($species as $specie) {
            $this->syncSpecies($specie);
        }

        Log::info('Species sync completed', $this->stats);

        return $this->stats;
    }

    /**
     * Sincronizar una especie específica
     */
    public function syncSpecies(Species $species): bool
    {
        $this->stats['processed']++;

        try {
            $species->update([
                'last_sync_attempt' => now(),
                'sync_status' => 'syncing',
            ]);

            // 1. Obtener datos de GBIF (taxonomía base)
            $gbifData = $this->fetchFromGbif($species->scientific_name);
            
            if ($gbifData) {
                $this->updateFromGbif($species, $gbifData);
            }

            // 2. Obtener datos de IUCN (conservación)
            if ($this->iucnToken) {
                $iucnData = $this->fetchFromIucn($species->scientific_name);
                if ($iucnData) {
                    $this->updateFromIucn($species, $iucnData);
                }
            }

            // 3. Obtener datos de CITES (comercio)
            if ($this->citesToken) {
                $citesData = $this->fetchFromCites($species->scientific_name);
                if ($citesData) {
                    $this->updateFromCites($species, $citesData);
                }
            }

            // Actualizar estado de protección
            $species->is_protected = $this->calculateProtectionStatus($species);
            
            $species->update([
                'synced_at' => now(),
                'sync_status' => 'synced',
                'sync_error' => null,
            ]);

            $this->stats['updated']++;
            return true;

        } catch (\Exception $e) {
            Log::error("Error syncing species {$species->scientific_name}", [
                'error' => $e->getMessage(),
            ]);

            $species->update([
                'sync_status' => 'error',
                'sync_error' => $e->getMessage(),
            ]);

            $this->stats['errors']++;
            return false;
        }
    }

    /**
     * Buscar y crear especie desde APIs
     */
    public function searchAndCreate(string $scientificName): ?Species
    {
        // Verificar si ya existe
        $existing = Species::where('scientific_name', $scientificName)->first();
        if ($existing) {
            return $existing;
        }

        try {
            // Buscar en GBIF
            $gbifData = $this->fetchFromGbif($scientificName);
            
            if (!$gbifData) {
                // Si no está en GBIF, crear como especie no protegida
                return Species::create([
                    'scientific_name' => $scientificName,
                    'is_protected' => false,
                    'sync_source' => 'manual',
                    'sync_status' => 'pending',
                    'manually_added' => true,
                ]);
            }

            // Crear especie con datos de GBIF
            $species = Species::create([
                'scientific_name' => $gbifData['canonicalName'] ?? $scientificName,
                'common_name' => $gbifData['vernacularName'] ?? null,
                'taxon_group' => $this->mapTaxonGroup($gbifData['class'] ?? null),
                'kingdom' => $gbifData['kingdom'] ?? null,
                'phylum' => $gbifData['phylum'] ?? null,
                'class' => $gbifData['class'] ?? null,
                'order' => $gbifData['order'] ?? null,
                'family' => $gbifData['family'] ?? null,
                'genus' => $gbifData['genus'] ?? null,
                'gbif_key' => $gbifData['key'] ?? null,
                'sync_source' => 'gbif',
                'sync_status' => 'pending',
                'source_json' => ['gbif' => $gbifData],
            ]);

            // Sincronizar datos adicionales
            $this->syncSpecies($species);

            $this->stats['created']++;
            return $species;

        } catch (\Exception $e) {
            Log::error("Error creating species {$scientificName}", [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Importar especies desde un listado
     */
    public function importFromList(array $speciesNames): array
    {
        $this->resetStats();

        foreach ($speciesNames as $name) {
            $name = trim($name);
            if (empty($name)) continue;

            $this->searchAndCreate($name);
            $this->stats['processed']++;
            
            // Rate limiting
            usleep(100000); // 100ms entre llamadas
        }

        return $this->stats;
    }

    /**
     * IMPORTACIÓN INICIAL - Carga masiva de especies de España desde GBIF
     * 
     * Este método está diseñado para la carga inicial de la base de datos.
     * Importa especies de diferentes grupos taxonómicos presentes en España.
     * 
     * Las especies NO protegidas también se importan ya que son necesarias
     * para los informes (el estado de protección afecta los cálculos).
     * 
     * @param callable|null $progressCallback Callback para mostrar progreso
     */
    public function initialImport(?callable $progressCallback = null): array
    {
        $this->resetStats();

        // Grupos taxonómicos a importar (clases principales)
        $taxonGroups = [
            // Vertebrados
            'Mammalia' => ['name' => 'Mamíferos', 'limit' => 200],
            'Aves' => ['name' => 'Aves', 'limit' => 300],
            'Reptilia' => ['name' => 'Reptiles', 'limit' => 100],
            'Amphibia' => ['name' => 'Anfibios', 'limit' => 50],
            'Actinopterygii' => ['name' => 'Peces', 'limit' => 150],
            // Invertebrados
            'Insecta' => ['name' => 'Invertebrados', 'limit' => 100],
            'Arachnida' => ['name' => 'Invertebrados', 'limit' => 30],
            'Malacostraca' => ['name' => 'Invertebrados', 'limit' => 30],
            // Flora
            'Magnoliopsida' => ['name' => 'Flora', 'limit' => 100],
            'Liliopsida' => ['name' => 'Flora', 'limit' => 50],
        ];

        $totalGroups = count($taxonGroups);
        $currentGroup = 0;

        foreach ($taxonGroups as $className => $config) {
            $currentGroup++;
            
            if ($progressCallback) {
                $progressCallback("Importando {$config['name']} ({$className})... [$currentGroup/$totalGroups]");
            }

            try {
                $this->importByClass($className, $config['name'], $config['limit']);
            } catch (\Exception $e) {
                Log::error("Error importing {$className}", ['error' => $e->getMessage()]);
            }

            // Pausa entre grupos para no sobrecargar la API
            sleep(1);
        }

        Log::info('Initial species import completed', $this->stats);

        return $this->stats;
    }

    /**
     * Importar especies por clase taxonómica desde GBIF
     */
    private function importByClass(string $className, string $taxonGroup, int $limit): void
    {
        $offset = 0;
        $pageSize = 100;
        $imported = 0;

        while ($imported < $limit) {
            try {
                // Usar endpoint de ocurrencias en España agrupadas por especie
                $response = Http::timeout(60)->get($this->gbifApiUrl . '/occurrence/search', [
                    'country' => 'ES',
                    'classKey' => $this->getGbifClassKey($className),
                    'limit' => 0,
                    'facet' => 'speciesKey',
                    'facetLimit' => min($pageSize, $limit - $imported),
                    'facetOffset' => $offset,
                ]);

                if (!$response->successful()) {
                    break;
                }

                $facets = $response->json()['facets'] ?? [];
                $speciesFacet = collect($facets)->firstWhere('field', 'SPECIES_KEY');
                
                if (!$speciesFacet || empty($speciesFacet['counts'])) {
                    break;
                }

                foreach ($speciesFacet['counts'] as $facetCount) {
                    $speciesKey = $facetCount['name'];
                    
                    // Obtener detalles de la especie
                    $speciesData = $this->fetchSpeciesByKey($speciesKey);
                    
                    if ($speciesData && isset($speciesData['canonicalName'])) {
                        $existing = Species::where('scientific_name', $speciesData['canonicalName'])->first();
                        
                        if (!$existing) {
                            $this->createSpeciesFromGbif($speciesData, $taxonGroup);
                            $imported++;
                        } else {
                            $this->stats['skipped']++;
                        }
                    }

                    // Rate limiting
                    usleep(50000);

                    if ($imported >= $limit) break;
                }

                $offset += $pageSize;

            } catch (\Exception $e) {
                Log::warning("Error in importByClass for {$className}", ['error' => $e->getMessage()]);
                break;
            }
        }
    }

    /**
     * Obtener especie por su key de GBIF
     */
    private function fetchSpeciesByKey(int $speciesKey): ?array
    {
        try {
            $response = Http::timeout(10)->get($this->gbifApiUrl . '/species/' . $speciesKey);
            
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            // Silently fail
        }
        
        return null;
    }

    /**
     * Crear especie desde datos de GBIF
     */
    private function createSpeciesFromGbif(array $data, string $taxonGroup): ?Species
    {
        try {
            $species = Species::create([
                'scientific_name' => $data['canonicalName'],
                'common_name' => $data['vernacularName'] ?? null,
                'taxon_group' => $taxonGroup,
                'kingdom' => $data['kingdom'] ?? null,
                'phylum' => $data['phylum'] ?? null,
                'class' => $data['class'] ?? null,
                'order' => $data['order'] ?? null,
                'family' => $data['family'] ?? null,
                'genus' => $data['genus'] ?? null,
                'gbif_key' => $data['key'] ?? $data['usageKey'] ?? null,
                'sync_source' => 'gbif',
                'sync_status' => 'pending', // Para que luego se enriquezca con IUCN/CITES
                'is_protected' => false, // Por defecto no protegida hasta verificar
                'source_json' => ['gbif' => $data],
            ]);

            $this->stats['created']++;
            return $species;

        } catch (\Exception $e) {
            Log::warning("Error creating species from GBIF", [
                'name' => $data['canonicalName'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            $this->stats['errors']++;
            return null;
        }
    }

    /**
     * Obtener el classKey de GBIF para una clase taxonómica
     */
    private function getGbifClassKey(string $className): ?int
    {
        $classKeys = [
            'Mammalia' => 359,
            'Aves' => 212,
            'Reptilia' => 358,
            'Amphibia' => 131,
            'Actinopterygii' => 204,
            'Insecta' => 216,
            'Arachnida' => 367,
            'Malacostraca' => 229,
            'Magnoliopsida' => 220,
            'Liliopsida' => 196,
        ];

        return $classKeys[$className] ?? null;
    }

    /**
     * Sincronizar especies populares de España desde GBIF (método legacy)
     * @deprecated Usar initialImport() para carga inicial
     */
    public function syncSpanishFauna(int $limit = 500): array
    {
        $this->resetStats();

        try {
            // Buscar especies con registros en España
            $response = Http::timeout(30)->get($this->gbifApiUrl . '/species/search', [
                'country' => 'ES',
                'status' => 'ACCEPTED',
                'limit' => $limit,
                'rank' => 'SPECIES',
            ]);

            if (!$response->successful()) {
                throw new \Exception('Error fetching Spanish fauna from GBIF');
            }

            $results = $response->json()['results'] ?? [];

            foreach ($results as $result) {
                $scientificName = $result['canonicalName'] ?? $result['scientificName'] ?? null;
                if ($scientificName) {
                    $this->searchAndCreate($scientificName);
                    usleep(50000); // 50ms rate limit
                }
            }

        } catch (\Exception $e) {
            Log::error('Error syncing Spanish fauna', ['error' => $e->getMessage()]);
            $this->stats['errors']++;
        }

        return $this->stats;
    }

    // ========================================
    // Métodos de APIs
    // ========================================

    /**
     * Obtener datos de GBIF
     */
    private function fetchFromGbif(string $scientificName): ?array
    {
        $cacheKey = 'gbif_' . md5($scientificName);
        
        return Cache::remember($cacheKey, 86400, function () use ($scientificName) {
            try {
                // Primero buscar match exacto
                $response = Http::timeout(10)->get($this->gbifApiUrl . '/species/match', [
                    'name' => $scientificName,
                    'strict' => false,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['matchType'] ?? '') !== 'NONE' && isset($data['usageKey'])) {
                        // Obtener datos completos
                        $detailResponse = Http::timeout(10)
                            ->get($this->gbifApiUrl . '/species/' . $data['usageKey']);
                        
                        if ($detailResponse->successful()) {
                            return array_merge($data, $detailResponse->json());
                        }
                        return $data;
                    }
                }
            } catch (\Exception $e) {
                Log::warning("GBIF API error for {$scientificName}", ['error' => $e->getMessage()]);
            }
            return null;
        });
    }

    /**
     * Obtener datos de IUCN Red List
     */
    private function fetchFromIucn(string $scientificName): ?array
    {
        if (!$this->iucnToken) return null;

        $cacheKey = 'iucn_' . md5($scientificName);
        
        return Cache::remember($cacheKey, 86400, function () use ($scientificName) {
            try {
                $response = Http::timeout(10)->get(
                    $this->iucnApiUrl . '/species/' . urlencode($scientificName),
                    ['token' => $this->iucnToken]
                );

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['result'][0] ?? null;
                }
            } catch (\Exception $e) {
                Log::warning("IUCN API error for {$scientificName}", ['error' => $e->getMessage()]);
            }
            return null;
        });
    }

    /**
     * Obtener datos de CITES Species+
     */
    private function fetchFromCites(string $scientificName): ?array
    {
        if (!$this->citesToken) return null;

        $cacheKey = 'cites_' . md5($scientificName);
        
        return Cache::remember($cacheKey, 86400, function () use ($scientificName) {
            try {
                $response = Http::timeout(10)
                    ->withHeaders(['X-Authentication-Token' => $this->citesToken])
                    ->get($this->citesApiUrl . '/taxon_concepts', [
                        'name' => $scientificName,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['taxon_concepts'][0] ?? null;
                }
            } catch (\Exception $e) {
                Log::warning("CITES API error for {$scientificName}", ['error' => $e->getMessage()]);
            }
            return null;
        });
    }

    // ========================================
    // Métodos de actualización
    // ========================================

    /**
     * Actualizar especie con datos de GBIF
     */
    private function updateFromGbif(Species $species, array $data): void
    {
        $species->update([
            'gbif_key' => $data['usageKey'] ?? $data['key'] ?? $species->gbif_key,
            'kingdom' => $data['kingdom'] ?? $species->kingdom,
            'phylum' => $data['phylum'] ?? $species->phylum,
            'class' => $data['class'] ?? $species->class,
            'order' => $data['order'] ?? $species->order,
            'family' => $data['family'] ?? $species->family,
            'genus' => $data['genus'] ?? $species->genus,
            'taxon_group' => $species->taxon_group ?: $this->mapTaxonGroup($data['class'] ?? null),
            'sync_source' => $species->sync_source ?: 'gbif',
            'source_json' => array_merge($species->source_json ?? [], ['gbif' => $data]),
        ]);
    }

    /**
     * Actualizar especie con datos de IUCN
     */
    private function updateFromIucn(Species $species, array $data): void
    {
        $species->update([
            'iucn_taxon_id' => $data['taxonid'] ?? $species->iucn_taxon_id,
            'iucn_category' => $data['category'] ?? $species->iucn_category,
            'iucn_assessment_year' => isset($data['assessment_date']) 
                ? (int) substr($data['assessment_date'], 0, 4) 
                : $species->iucn_assessment_year,
            'source_json' => array_merge($species->source_json ?? [], ['iucn' => $data]),
        ]);
    }

    /**
     * Actualizar especie con datos de CITES
     */
    private function updateFromCites(Species $species, array $data): void
    {
        $appendix = null;
        if (isset($data['cites_listings'])) {
            foreach ($data['cites_listings'] as $listing) {
                if ($listing['is_current'] ?? false) {
                    $appendix = $listing['appendix'] ?? null;
                    break;
                }
            }
        }

        $species->update([
            'cites_id' => $data['id'] ?? $species->cites_id,
            'cites_appendix' => $appendix ?? $species->cites_appendix,
            'source_json' => array_merge($species->source_json ?? [], ['cites' => $data]),
        ]);
    }

    // ========================================
    // Métodos auxiliares
    // ========================================

    /**
     * Calcular estado de protección
     */
    private function calculateProtectionStatus(Species $species): bool
    {
        // Protegida si tiene:
        // - Estado BOE
        // - Estado CCAA
        // - Categoría IUCN de amenaza (CR, EN, VU, NT)
        // - Apéndice CITES
        return !empty($species->boe_status) 
            || !empty($species->ccaa_status) 
            || in_array($species->iucn_category, ['CR', 'EN', 'VU', 'NT'])
            || !empty($species->cites_appendix);
    }

    /**
     * Mapear clase taxonómica a grupo
     */
    private function mapTaxonGroup(?string $class): ?string
    {
        if (!$class) return null;

        $mapping = [
            'Mammalia' => 'Mamíferos',
            'Aves' => 'Aves',
            'Reptilia' => 'Reptiles',
            'Amphibia' => 'Anfibios',
            'Actinopterygii' => 'Peces',
            'Chondrichthyes' => 'Peces',
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

        return $mapping[$class] ?? null;
    }

    /**
     * Resetear estadísticas
     */
    private function resetStats(): void
    {
        $this->stats = [
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
            'skipped' => 0,
        ];
    }

    /**
     * Obtener estadísticas actuales
     */
    public function getStats(): array
    {
        return $this->stats;
    }

    /**
     * Verificar estado de las APIs
     */
    public function checkApiStatus(): array
    {
        $status = [];

        // GBIF
        try {
            $response = Http::timeout(5)->get($this->gbifApiUrl . '/species/1');
            $status['gbif'] = [
                'available' => $response->successful(),
                'token_required' => false,
                'token_configured' => true,
            ];
        } catch (\Exception $e) {
            $status['gbif'] = ['available' => false, 'error' => $e->getMessage()];
        }

        // IUCN
        $status['iucn'] = [
            'available' => !empty($this->iucnToken),
            'token_required' => true,
            'token_configured' => !empty($this->iucnToken),
        ];

        // CITES
        $status['cites'] = [
            'available' => !empty($this->citesToken),
            'token_required' => true,
            'token_configured' => !empty($this->citesToken),
        ];

        return $status;
    }
}
