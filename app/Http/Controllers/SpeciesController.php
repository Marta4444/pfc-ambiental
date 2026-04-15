<?php

namespace App\Http\Controllers;

use App\Models\Species;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SpeciesController extends Controller
{
    /**
     * Búsqueda de especies (para autocompletado AJAX)
     * Accesible por todos los usuarios autenticados
     * 
     * Si no encuentra resultados locales y el usuario busca un nombre científico,
     * busca en GBIF y ofrece la opción de crear la especie.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'q' => 'required|string|min:2|max:255',
                'limit' => 'integer|min:1|max:50',
                'include_external' => 'boolean', // Si buscar en GBIF cuando no hay resultados locales
            ]);

            $term = $request->input('q');
            $limit = (int) $request->input('limit', 10);
            $includeExternal = $request->boolean('include_external', true);

            // Primero buscar en base de datos local
            $species = Species::search($term)
                ->select([
                    'id',
                    'scientific_name',
                    'common_name',
                    'taxon_group',
                    'boe_status',
                    'boe_law_ref',
                    'ccaa_status',
                    'iucn_category',
                    'cites_appendix',
                    'is_protected',
                ])
                ->orderByRaw('is_protected DESC')
                ->orderBy('scientific_name')
                ->limit($limit)
                ->get();

            $localResults = $species->map(fn($sp) => $this->formatSpeciesResult($sp, 'local'));

            // Si no hay resultados locales y se permite buscar externamente
            $externalResults = collect();
            if ($localResults->isEmpty() && $includeExternal && strlen($term) >= 3) {
                $externalResults = $this->searchExternalApis($term, $limit);
            }

            return response()->json([
                'success' => true,
                'data' => $localResults->merge($externalResults),
                'count' => $localResults->count(),
                'external_count' => $externalResults->count(),
                'has_external' => $externalResults->isNotEmpty(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validación fallida',
                'messages' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en búsqueda de especies: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'message' => config('app.debug') ? $e->getMessage() : 'Ocurrió un error al buscar especies',
            ], 500);
        }
    }

    /**
     * Buscar en APIs externas (GBIF)
     */
    private function searchExternalApis(string $term, int $limit): \Illuminate\Support\Collection
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->get('https://api.gbif.org/v1/species/suggest', [
                    'q' => $term,
                    'limit' => $limit,
                ]);

            if (!$response->successful()) {
                return collect();
            }

            return collect($response->json())
                ->filter(fn($item) => ($item['rank'] ?? '') === 'SPECIES')
                ->take($limit)
                ->map(function ($item) {
                    return [
                        'id' => null, // No existe en BD
                        'gbif_key' => $item['key'] ?? null,
                        'scientific_name' => $item['canonicalName'] ?? $item['scientificName'] ?? '',
                        'common_name' => $item['vernacularName'] ?? null,
                        'taxon_group' => $this->mapClassToTaxonGroup($item['class'] ?? null),
                        'is_protected' => false,
                        'boe_status' => null,
                        'boe_law_ref' => null,
                        'ccaa_status' => null,
                        'iucn_category' => null,
                        'iucn_label' => null,
                        'cites_appendix' => null,
                        'has_boe_data' => false,
                        'has_ccaa_data' => false,
                        'has_iucn_data' => false,
                        'has_cites_data' => false,
                        'label' => ($item['canonicalName'] ?? $item['scientificName'] ?? '') . ' (desde GBIF)',
                        'source' => 'gbif',
                        'is_external' => true,
                        // Datos adicionales de GBIF para crear después
                        'gbif_data' => [
                            'kingdom' => $item['kingdom'] ?? null,
                            'phylum' => $item['phylum'] ?? null,
                            'class' => $item['class'] ?? null,
                            'order' => $item['order'] ?? null,
                            'family' => $item['family'] ?? null,
                            'genus' => $item['genus'] ?? null,
                        ],
                    ];
                });
        } catch (\Exception $e) {
            Log::warning('Error buscando en GBIF: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Formatear resultado de especie para respuesta JSON
     */
    private function formatSpeciesResult(Species $sp, string $source): array
    {
        $ccaaStatus = $sp->ccaa_status;
        if (is_string($ccaaStatus) && !empty($ccaaStatus)) {
            $ccaaStatus = [$ccaaStatus];
        }

        return [
            'id' => $sp->id,
            'scientific_name' => $sp->scientific_name,
            'common_name' => $sp->common_name,
            'taxon_group' => $sp->taxon_group,
            'is_protected' => $sp->is_protected,
            'boe_status' => $sp->boe_status,
            'boe_law_ref' => $sp->boe_law_ref,
            'ccaa_status' => $ccaaStatus,
            'iucn_category' => $sp->iucn_category,
            'iucn_label' => Species::IUCN_CATEGORIES[$sp->iucn_category] ?? null,
            'cites_appendix' => $sp->cites_appendix,
            'has_boe_data' => !empty($sp->boe_status),
            'has_ccaa_data' => !empty($sp->ccaa_status),
            'has_iucn_data' => !empty($sp->iucn_category),
            'has_cites_data' => !empty($sp->cites_appendix),
            'label' => $sp->scientific_name . ($sp->common_name ? " ({$sp->common_name})" : ''),
            'source' => $source,
            'is_external' => false,
        ];
    }

    /**
     * Mapear clase taxonómica a grupo
     */
    private function mapClassToTaxonGroup(?string $class): ?string
    {
        if (!$class) return null;
        
        $map = [
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
        ];

        return $map[$class] ?? null;
    }

}