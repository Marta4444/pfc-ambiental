<?php

namespace App\Http\Controllers;

use App\Models\ProtectedArea;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProtectedAreaController extends Controller
{
    /**
     * Verificar si unas coordenadas están en un área protegida (AJAX)
     */
    public function checkCoordinates(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'long' => 'required|numeric|between:-180,180',
        ]);

        $lat = (float) $request->input('lat');
        $long = (float) $request->input('long');

        // Buscar áreas que contengan el punto (por bounding box primero)
        $potentialAreas = ProtectedArea::containingPoint($lat, $long)->get();

        // Verificar con geometría precisa si está disponible
        $matchingAreas = $potentialAreas->filter(function ($area) use ($lat, $long) {
            return $area->containsPoint($lat, $long);
        });

        if ($matchingAreas->isEmpty()) {
            return response()->json([
                'success' => true,
                'coordinates' => [
                    'lat' => $lat,
                    'long' => $long,
                ],
                'in_protected_area' => false,
                'areas' => [],
                'message' => 'Las coordenadas no están dentro de ningún área protegida registrada.',
            ]);
        }

        return response()->json([
            'success' => true,
            'coordinates' => [
                'lat' => $lat,
                'long' => $long,
            ],
            'in_protected_area' => true,
            'areas' => $matchingAreas->map(function ($area) {
                return [
                    'id' => $area->id,
                    'name' => $area->name,
                    'protection_type' => $area->protection_type,
                    'iucn_category' => $area->iucn_category,
                    'iucn_label' => ProtectedArea::IUCN_CATEGORIES[$area->iucn_category] ?? null,
                    'designation' => $area->designation,
                    'region' => $area->region,
                    'area_km2' => $area->area_km2,
                ];
            })->values(),
            'message' => 'Las coordenadas están dentro de ' . $matchingAreas->count() . ' área(s) protegida(s).',
        ]);
    }

    /**
     * Listado de áreas protegidas (admin)
     */
    public function index(Request $request)
    {
        $query = ProtectedArea::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('designation', 'LIKE', '%' . $request->search . '%');
            });
        }

        if ($request->filled('protection_type')) {
            $query->byProtectionType($request->protection_type);
        }

        if ($request->filled('region')) {
            $query->byRegion($request->region);
        }

        if ($request->filled('active')) {
            $query->where('active', $request->active === 'true');
        }

        $areas = $query->orderBy('name')->paginate(25);

        return view('protected-areas.index', [
            'areas' => $areas,
            'protectionTypes' => ProtectedArea::PROTECTION_TYPES,
            'regions' => ProtectedArea::distinct()->pluck('region')->filter()->sort()->values(),
        ]);
    }

    /**
     * Mostrar detalle de un área protegida
     */
    public function show(ProtectedArea $protectedArea): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $protectedArea->id,
                'name' => $protectedArea->name,
                'protection_type' => $protectedArea->protection_type,
                'iucn_category' => $protectedArea->iucn_category,
                'iucn_label' => ProtectedArea::IUCN_CATEGORIES[$protectedArea->iucn_category] ?? null,
                'designation' => $protectedArea->designation,
                'description' => $protectedArea->description,
                'region' => $protectedArea->region,
                'area_km2' => $protectedArea->area_km2,
                'established_year' => $protectedArea->established_year,
                'bounding_box' => [
                    'lat_min' => $protectedArea->lat_min,
                    'lat_max' => $protectedArea->lat_max,
                    'long_min' => $protectedArea->long_min,
                    'long_max' => $protectedArea->long_max,
                ],
                'active' => $protectedArea->active,
            ],
        ]);
    }

    /**
     * Búsqueda de áreas por nombre (autocompletado AJAX)
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'limit' => 'integer|min:1|max:50',
        ]);

        $term = $request->input('q');
        $limit = $request->input('limit', 10);

        $areas = ProtectedArea::where('active', true)
            ->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('designation', 'LIKE', "%{$term}%")
                    ->orWhere('region', 'LIKE', "%{$term}%");
            })
            ->select(['id', 'name', 'protection_type', 'iucn_category', 'region', 'area_km2'])
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $areas->map(function ($area) {
                return [
                    'id' => $area->id,
                    'name' => $area->name,
                    'protection_type' => $area->protection_type,
                    'iucn_category' => $area->iucn_category,
                    'region' => $area->region,
                    'area_km2' => $area->area_km2,
                    'label' => $area->name . ($area->region ? " ({$area->region})" : ''),
                ];
            }),
            'count' => $areas->count(),
        ]);
    }

    /**
     * Crear nueva área protegida (admin)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:protected_areas,name',
            'protection_type' => 'required|string|in:' . implode(',', ProtectedArea::PROTECTION_TYPES),
            'iucn_category' => 'nullable|string|in:' . implode(',', array_keys(ProtectedArea::IUCN_CATEGORIES)),
            'designation' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'region' => 'nullable|string|max:100',
            'area_km2' => 'nullable|numeric|min:0',
            'established_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            'lat_min' => 'nullable|numeric|between:-90,90',
            'lat_max' => 'nullable|numeric|between:-90,90',
            'long_min' => 'nullable|numeric|between:-180,180',
            'long_max' => 'nullable|numeric|between:-180,180',
            'active' => 'boolean',
        ]);

        $area = ProtectedArea::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Área protegida creada correctamente.',
                'data' => $area,
            ], 201);
        }

        return redirect()->route('protected-areas.index')
            ->with('success', 'Área protegida creada correctamente.');
    }

    /**
     * Actualizar área protegida (admin)
     */
    public function update(Request $request, ProtectedArea $protectedArea)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:protected_areas,name,' . $protectedArea->id,
            'protection_type' => 'required|string|in:' . implode(',', ProtectedArea::PROTECTION_TYPES),
            'iucn_category' => 'nullable|string|in:' . implode(',', array_keys(ProtectedArea::IUCN_CATEGORIES)),
            'designation' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'region' => 'nullable|string|max:100',
            'area_km2' => 'nullable|numeric|min:0',
            'established_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            'lat_min' => 'nullable|numeric|between:-90,90',
            'lat_max' => 'nullable|numeric|between:-90,90',
            'long_min' => 'nullable|numeric|between:-180,180',
            'long_max' => 'nullable|numeric|between:-180,180',
            'active' => 'boolean',
        ]);

        $protectedArea->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Área protegida actualizada correctamente.',
                'data' => $protectedArea,
            ]);
        }

        return redirect()->route('protected-areas.index')
            ->with('success', 'Área protegida actualizada correctamente.');
    }

    /**
     * Eliminar área protegida (admin)
     */
    public function destroy(ProtectedArea $protectedArea)
    {
        $protectedArea->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Área protegida eliminada correctamente.',
            ]);
        }

        return redirect()->route('protected-areas.index')
            ->with('success', 'Área protegida eliminada correctamente.');
    }
}