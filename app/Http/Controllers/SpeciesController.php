<?php

namespace App\Http\Controllers;

use App\Models\Species;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SpeciesController extends Controller
{
    /**
     * Búsqueda de especies (para autocompletado AJAX)
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'limit' => 'integer|min:1|max:50',
        ]);

        $term = $request->input('q');
        $limit = $request->input('limit', 10);

        $species = Species::search($term)
            ->select([
                'id',
                'scientific_name',
                'common_name',
                'taxon_group',
                'boe_status',
                'iucn_category',
                'cites_appendix',
                'is_protected',
            ])
            ->orderByRaw('is_protected DESC') // Protegidas primero
            ->orderBy('scientific_name')
            ->limit($limit)
            ->get();

        // Si no hay resultados locales, podríamos buscar en API externa
        // TODO: Implementar búsqueda en GBIF/Catalogue of Life si no hay resultados

        return response()->json([
            'success' => true,
            'data' => $species->map(function ($sp) {
                return [
                    'id' => $sp->id,
                    'scientific_name' => $sp->scientific_name,
                    'common_name' => $sp->common_name,
                    'taxon_group' => $sp->taxon_group,
                    'is_protected' => $sp->is_protected,
                    'protection_summary' => $sp->protection_summary,
                    'label' => $sp->scientific_name . ($sp->common_name ? " ({$sp->common_name})" : ''),
                ];
            }),
            'count' => $species->count(),
        ]);
    }

    /**
     * Obtener detalles completos de una especie
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
                'iucn_assessment_year' => $species->iucn_assessment_year,
                'cites_appendix' => $species->cites_appendix,
                'is_protected' => $species->is_protected,
                'highest_protection' => $species->highest_protection,
                'protection_summary' => $species->protection_summary,
                'synced_at' => $species->synced_at?->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * Listado de especies (admin)
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

        if ($request->filled('protected_only') && $request->protected_only) {
            $query->protected();
        }

        $species = $query->orderBy('scientific_name')->paginate(25);

        return view('species.index', [
            'species' => $species,
            'taxonGroups' => Species::TAXON_GROUPS,
        ]);
    }

    /**
     * Validar/verificar protección de una especie en una CCAA específica
     */
    public function checkProtection(Request $request): JsonResponse
    {
        $request->validate([
            'species_id' => 'required|exists:species,id',
            'ccaa' => 'nullable|string|max:100',
        ]);

        $species = Species::find($request->species_id);
        $ccaa = $request->input('ccaa');

        $response = [
            'species' => $species->scientific_name,
            'is_protected' => $species->is_protected,
            'national' => [
                'protected' => !empty($species->boe_status),
                'status' => $species->boe_status,
                'law' => $species->boe_law_ref,
            ],
            'iucn' => [
                'category' => $species->iucn_category,
                'label' => Species::IUCN_CATEGORIES[$species->iucn_category] ?? null,
            ],
            'cites' => [
                'appendix' => $species->cites_appendix,
            ],
        ];

        if ($ccaa && $species->ccaa_status) {
            $response['ccaa'] = [
                'name' => $ccaa,
                'protected' => isset($species->ccaa_status[$ccaa]),
                'status' => $species->ccaa_status[$ccaa] ?? null,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }
}
