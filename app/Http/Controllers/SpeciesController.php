<?php

namespace App\Http\Controllers;

use App\Models\Species;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SpeciesController extends Controller
{
    /**
     * Búsqueda de especies (para autocompletado AJAX)
     * Accesible por todos los usuarios autenticados
     */
    public function search(Request $request): JsonResponse
    {
        try {
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

            return response()->json([
                'success' => true,
                'data' => $species->map(function ($sp) {
                    // Manejar ccaa_status que puede ser string o array
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
                        // Datos de protección para autorellenar
                        'boe_status' => $sp->boe_status,
                        'boe_law_ref' => $sp->boe_law_ref,
                        'ccaa_status' => $ccaaStatus,
                        'iucn_category' => $sp->iucn_category,
                        'iucn_label' => Species::IUCN_CATEGORIES[$sp->iucn_category] ?? null,
                        'cites_appendix' => $sp->cites_appendix,
                        // Indicadores de qué campos tienen datos (para saber cuáles son editables)
                        'has_boe_data' => !empty($sp->boe_status),
                        'has_ccaa_data' => !empty($sp->ccaa_status),
                        'has_iucn_data' => !empty($sp->iucn_category),
                        'label' => $sp->scientific_name . ($sp->common_name ? " ({$sp->common_name})" : ''),
                    ];
                }),
                'count' => $species->count(),
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
            'boeStatuses' => Species::BOE_STATUSES ?? [],
            'iucnCategories' => Species::IUCN_CATEGORIES,
            'citesAppendices' => Species::CITES_APPENDICES ?? ['I', 'II', 'III'],
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
            'boe_status' => 'nullable|string|max:255',
            'boe_law_ref' => 'nullable|string|max:255',
            'ccaa_status' => 'nullable|string|max:255',
            'iucn_category' => 'nullable|string|max:10',
            'cites_appendix' => 'nullable|string|max:50',
        ]);

        $species->fill($validated);

        // Recalcular is_protected
        $species->is_protected = !empty($species->boe_status) 
            || !empty($species->cites_appendix)
            || in_array($species->iucn_category, ['CR', 'EN', 'VU', 'NT']);

        $species->save();

        return redirect()->route('species.index')
            ->with('success', 'Especie actualizada correctamente.');
    }
}