<?php

namespace App\Http\Controllers;

use App\Models\ProtectedArea;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProtectedAreaController extends Controller
{
    /**
     * Verificar si unas coordenadas están en área protegida (AJAX)
     */
    public function checkCoordinates(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'long' => 'required|numeric|between:-180,180',
        ]);

        $lat = (float) $request->input('lat');
        $long = (float) $request->input('long');

        $protectionInfo = ProtectedArea::getProtectionInfo($lat, $long);

        return response()->json([
            'success' => true,
            'coordinates' => [
                'lat' => $lat,
                'long' => $long,
            ],
            'protection' => $protectionInfo,
        ]);
    }

    /**
     * Listado de áreas protegidas (admin)
     */
    public function index(Request $request)
    {
        $query = ProtectedArea::query();

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('protection_type')) {
            $query->byProtectionType($request->protection_type);
        }

        if ($request->filled('region')) {
            $query->byRegion($request->region);
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
            'data' => $protectedArea,
        ]);
    }

    /**
     * Búsqueda de áreas por nombre (autocompletado)
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'limit' => 'integer|min:1|max:50',
        ]);

        $term = $request->input('q');
        $limit = $request->input('limit', 10);

        $areas = ProtectedArea::active()
            ->where('name', 'LIKE', "%{$term}%")
            ->select(['id', 'name', 'protection_type', 'region'])
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $areas,
            'count' => $areas->count(),
        ]);
    }
}