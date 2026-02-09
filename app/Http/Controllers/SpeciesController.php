<?php

namespace App\Http\Controllers;

use App\Models\Species;
use App\Services\SpeciesSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            $limit = $request->input('limit', 10);
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
            \Log::error('Error en búsqueda de especies: ' . $e->getMessage());
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
            \Log::warning('Error buscando en GBIF: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Crear especie que no existe en la BD (desde búsqueda externa)
     * Llamado cuando el usuario selecciona una especie de GBIF
     */
    public function findOrCreate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'scientific_name' => 'required|string|max:255',
            'gbif_key' => 'nullable|integer',
            'common_name' => 'nullable|string|max:255',
            'taxon_group' => 'nullable|string|max:100',
            'gbif_data' => 'nullable|array',
        ]);

        // Buscar si ya existe
        $species = Species::where('scientific_name', $validated['scientific_name'])->first();

        if (!$species) {
            // Crear nueva especie como NO PROTEGIDA
            $species = Species::create([
                'scientific_name' => $validated['scientific_name'],
                'common_name' => $validated['common_name'] ?? null,
                'taxon_group' => $validated['taxon_group'] ?? null,
                'gbif_key' => $validated['gbif_key'] ?? null,
                'kingdom' => $validated['gbif_data']['kingdom'] ?? null,
                'phylum' => $validated['gbif_data']['phylum'] ?? null,
                'class' => $validated['gbif_data']['class'] ?? null,
                'order' => $validated['gbif_data']['order'] ?? null,
                'family' => $validated['gbif_data']['family'] ?? null,
                'genus' => $validated['gbif_data']['genus'] ?? null,
                'is_protected' => false,
                'boe_status' => null, // Usuario puede editar
                'sync_source' => 'gbif',
                'sync_status' => 'synced',
                'manually_added' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatSpeciesResult($species, 'local'),
            'created' => $species->wasRecentlyCreated,
        ]);
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

    /**
     * Obtener detalles de una especie (respuesta JSON para AJAX)
     * Accesible por todos los usuarios autenticados
     */
    public function show(Species $species): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $species->id,
                'scientific_name' => $species->scientific_name,
                'common_name' => $species->common_name,
                'taxon_group' => $species->taxon_group,
                'boe_status' => $species->boe_status,
                'boe_law_ref' => $species->boe_law_ref,
                'ccaa_status' => $species->ccaa_status,
                'iucn_category' => $species->iucn_category,
                'iucn_label' => Species::IUCN_CATEGORIES[$species->iucn_category] ?? null,
                'cites_appendix' => $species->cites_appendix,
                'is_protected' => $species->is_protected,
                'has_boe_data' => !empty($species->boe_status),
                'has_ccaa_data' => !empty($species->ccaa_status),
                'has_iucn_data' => !empty($species->iucn_category),
            ],
        ]);
    }

  
    // MÉTODOS DE ADMINISTRACIÓN (solo admin)

    /**
     * Listado de especies (admin)
     * Protegido por AdminMiddleware en web.php
     */
    public function index(Request $request)
    {
        $query = Species::query();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('taxon_group')) {
            $query->byTaxonGroup($request->taxon_group);
        }

        if ($request->filled('is_protected')) {
            $query->where('is_protected', $request->is_protected === 'true');
        }

        if ($request->filled('has_boe')) {
            if ($request->has_boe === 'true') {
                $query->whereNotNull('boe_status')->where('boe_status', '!=', '');
            } else {
                $query->where(function ($q) {
                    $q->whereNull('boe_status')->orWhere('boe_status', '');
                });
            }
        }

        $species = $query->orderBy('scientific_name')->paginate(25);

        return view('species.index', [
            'species' => $species,
            'taxonGroups' => Species::TAXON_GROUPS,
            'iucnCategories' => Species::IUCN_CATEGORIES,
        ]);
    }

    /**
     * Ver detalles de una especie (admin - vista HTML)
     * Protegido por AdminMiddleware en web.php
     */
    public function adminShow(Species $species)
    {
        return view('species.show', [
            'species' => $species,
            'iucnCategories' => Species::IUCN_CATEGORIES,
        ]);
    }

    /**
     * Formulario de edición de especie (admin)
     * Protegido por AdminMiddleware en web.php
     */
    public function edit(Species $species)
    {
        return view('species.edit', [
            'species' => $species,
            'taxonGroups' => Species::TAXON_GROUPS,
            'boeStatuses' => Species::BOE_STATUSES,
            'ccaaStatuses' => Species::CCAA_STATUSES,
            'iucnCategories' => Species::IUCN_CATEGORIES,
            'citesAppendices' => Species::CITES_APPENDICES,
        ]);
    }

    /**
     * Actualizar especie (admin)
     * Protegido por AdminMiddleware en web.php
     */
    public function update(Request $request, Species $species)
    {
        $validated = $request->validate([
            'scientific_name' => 'required|string|max:255',
            'common_name' => 'nullable|string|max:255',
            'taxon_group' => 'nullable|string|max:100',
            'boe_status' => ['nullable', 'string', Rule::in(array_keys(Species::BOE_STATUSES))],
            'boe_law_ref' => 'nullable|string|max:255',
            'ccaa_status' => ['nullable', 'string', Rule::in(array_keys(Species::CCAA_STATUSES))],
            'iucn_category' => ['nullable', 'string', Rule::in(array_keys(Species::IUCN_CATEGORIES))],
            'cites_appendix' => ['nullable', 'string', Rule::in(array_keys(Species::CITES_APPENDICES))],
        ]);

        $species->fill($validated);

        // Recalcular is_protected usando métodos de validación del modelo
        $species->is_protected = Species::isValidBoeStatus($species->boe_status) 
            || Species::isValidCcaaStatus($species->ccaa_status)
            || Species::isValidCitesAppendix($species->cites_appendix)
            || Species::isProtectedIucnCategory($species->iucn_category);

        $species->save();

        return redirect()->route('species.index')
            ->with('success', 'Especie actualizada correctamente.');
    }

    /**
     * Verificar estado de protección de una especie
     * Usado por AJAX para obtener datos de protección
     */
    public function checkProtection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'species_id' => 'nullable|exists:species,id',
            'scientific_name' => 'nullable|string|max:255',
        ]);

        $species = null;

        // Buscar por ID o nombre científico
        if (!empty($validated['species_id'])) {
            $species = Species::find($validated['species_id']);
        } elseif (!empty($validated['scientific_name'])) {
            $species = Species::where('scientific_name', $validated['scientific_name'])->first();
        }

        if (!$species) {
            return response()->json([
                'success' => true,
                'found' => false,
                'is_protected' => false,
                'message' => 'Especie no encontrada en la base de datos. Se tratará como no protegida.',
                'data' => [
                    'boe_status' => null,
                    'boe_law_ref' => null,
                    'ccaa_status' => null,
                    'iucn_category' => null,
                    'cites_appendix' => null,
                    'is_protected' => false,
                    'protection_label' => 'No protegida',
                ],
            ]);
        }

        // Determinar etiqueta de protección
        $protectionLabel = 'No protegida';
        if ($species->is_protected) {
            if (!empty($species->boe_status)) {
                $protectionLabel = $species->boe_status;
            } elseif (!empty($species->iucn_category)) {
                $protectionLabel = 'IUCN: ' . (Species::IUCN_CATEGORIES[$species->iucn_category] ?? $species->iucn_category);
            } elseif (!empty($species->cites_appendix)) {
                $protectionLabel = 'CITES Apéndice ' . $species->cites_appendix;
            } else {
                $protectionLabel = 'Protegida';
            }
        }

        return response()->json([
            'success' => true,
            'found' => true,
            'is_protected' => $species->is_protected,
            'data' => [
                'id' => $species->id,
                'scientific_name' => $species->scientific_name,
                'common_name' => $species->common_name,
                'boe_status' => $species->boe_status,
                'boe_law_ref' => $species->boe_law_ref,
                'ccaa_status' => $species->ccaa_status,
                'iucn_category' => $species->iucn_category,
                'iucn_label' => Species::IUCN_CATEGORIES[$species->iucn_category] ?? null,
                'cites_appendix' => $species->cites_appendix,
                'is_protected' => $species->is_protected,
                'protection_label' => $protectionLabel,
                // Indicadores para el frontend
                'has_boe_data' => !empty($species->boe_status),
                'has_ccaa_data' => !empty($species->ccaa_status),
            ],
        ]);
    }
}