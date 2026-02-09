<?php

namespace App\Http\Controllers;

use App\Models\Species;
use App\Services\SpeciesSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SpeciesAdminController extends Controller
{
    protected SpeciesSyncService $syncService;

    public function __construct(SpeciesSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Panel principal de gestión de especies
     */
    public function index(Request $request): View
    {
        $query = Species::query();

        // Filtros
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('scientific_name', 'LIKE', "%{$search}%")
                  ->orWhere('common_name', 'LIKE', "%{$search}%");
            });
        }

        if ($taxonGroup = $request->get('taxon_group')) {
            $query->where('taxon_group', $taxonGroup);
        }

        if ($protected = $request->get('protected')) {
            $query->where('is_protected', $protected === 'yes');
        }

        if ($syncStatus = $request->get('sync_status')) {
            $query->where('sync_status', $syncStatus);
        }

        // Estadísticas
        $stats = [
            'total' => Species::count(),
            'protected' => Species::where('is_protected', true)->count(),
            'not_protected' => Species::where('is_protected', false)->count(),
            'synced' => Species::where('sync_status', 'synced')->count(),
            'pending' => Species::where('sync_status', 'pending')->count(),
            'errors' => Species::where('sync_status', 'error')->count(),
            'by_taxon' => Species::selectRaw('taxon_group, COUNT(*) as count')
                ->whereNotNull('taxon_group')
                ->groupBy('taxon_group')
                ->pluck('count', 'taxon_group'),
        ];

        // Estado de APIs
        $apiStatus = $this->syncService->checkApiStatus();

        $species = $query->orderBy('scientific_name')->paginate(25);

        return view('admin.species.index', compact('species', 'stats', 'apiStatus'));
    }

    /**
     * Formulario para crear especie manual
     */
    public function create(): View
    {
        return view('admin.species.create');
    }

    /**
     * Guardar especie manual
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'scientific_name' => 'required|string|max:255|unique:species,scientific_name',
            'common_name' => 'nullable|string|max:255',
            'taxon_group' => 'nullable|string|max:100',
            'boe_status' => 'nullable|string|max:100',
            'boe_law_ref' => 'nullable|string|max:255',
            'iucn_category' => 'nullable|string|max:20',
            'cites_appendix' => 'nullable|string|max:10',
        ]);

        $validated['manually_added'] = true;
        $validated['sync_source'] = 'manual';
        $validated['sync_status'] = 'synced';
        $validated['synced_at'] = now();

        // Calcular si está protegida
        $validated['is_protected'] = !empty($validated['boe_status']) 
            || in_array($validated['iucn_category'] ?? '', ['CR', 'EN', 'VU', 'NT'])
            || !empty($validated['cites_appendix']);

        Species::create($validated);

        return redirect()->route('admin.species.index')
            ->with('success', 'Especie creada correctamente.');
    }

    /**
     * Formulario de edición
     */
    public function edit(Species $species): View
    {
        return view('admin.species.edit', [
            'species' => $species,
            'taxonGroups' => Species::TAXON_GROUPS,
            'boeStatuses' => Species::BOE_STATUSES,
            'ccaaStatuses' => Species::CCAA_STATUSES,
            'iucnCategories' => Species::IUCN_CATEGORIES,
            'citesAppendices' => Species::CITES_APPENDICES,
        ]);
    }

    /**
     * Actualizar especie
     */
    public function update(Request $request, Species $species): RedirectResponse
    {
        $validated = $request->validate([
            'scientific_name' => 'required|string|max:255|unique:species,scientific_name,' . $species->id,
            'common_name' => 'nullable|string|max:255',
            'taxon_group' => ['nullable', 'string', Rule::in(Species::TAXON_GROUPS)],
            'boe_status' => ['nullable', 'string', Rule::in(array_keys(Species::BOE_STATUSES))],
            'boe_law_ref' => 'nullable|string|max:255',
            'ccaa_status' => ['nullable', 'string', Rule::in(array_keys(Species::CCAA_STATUSES))],
            'iucn_category' => ['nullable', 'string', Rule::in(array_keys(Species::IUCN_CATEGORIES))],
            'cites_appendix' => ['nullable', 'string', Rule::in(array_keys(Species::CITES_APPENDICES))],
        ]);

        // Recalcular protección usando métodos de validación del modelo
        $validated['is_protected'] = Species::isValidBoeStatus($validated['boe_status'] ?? null) 
            || Species::isValidCcaaStatus($validated['ccaa_status'] ?? null)
            || Species::isProtectedIucnCategory($validated['iucn_category'] ?? null)
            || Species::isValidCitesAppendix($validated['cites_appendix'] ?? null);

        $species->update($validated);

        return redirect()->route('admin.species.index')
            ->with('success', 'Especie actualizada correctamente.');
    }

    /**
     * Eliminar especie
     */
    public function destroy(Species $species): RedirectResponse
    {
        // Verificar si está en uso
        if ($species->reportDetails()->exists()) {
            return redirect()->route('admin.species.index')
                ->with('error', 'No se puede eliminar: la especie está siendo utilizada en casos.');
        }

        $species->delete();

        return redirect()->route('admin.species.index')
            ->with('success', 'Especie eliminada correctamente.');
    }

    /**
     * Sincronizar una especie específica
     */
    public function sync(Species $species): RedirectResponse
    {
        $result = $this->syncService->syncSpecies($species);

        if ($result) {
            return redirect()->back()
                ->with('success', "Especie '{$species->scientific_name}' sincronizada correctamente.");
        }

        return redirect()->back()
            ->with('error', "Error al sincronizar: {$species->sync_error}");
    }

    /**
     * Sincronizar todas las especies pendientes
     */
    public function syncAll(Request $request): RedirectResponse
    {
        $limit = $request->get('limit', 100);
        $force = $request->boolean('force');

        $stats = $this->syncService->syncAll($limit, $force);

        return redirect()->route('admin.species.index')
            ->with('success', "Sincronización completada: {$stats['updated']} actualizadas, {$stats['errors']} errores.");
    }

    /**
     * Buscar especie en APIs y crearla
     */
    public function search(Request $request): JsonResponse
    {
        $name = $request->get('name');

        if (empty($name) || strlen($name) < 3) {
            return response()->json(['error' => 'Nombre demasiado corto'], 400);
        }

        $species = $this->syncService->searchAndCreate($name);

        if ($species) {
            return response()->json([
                'success' => true,
                'species' => $species,
            ]);
        }

        return response()->json(['error' => 'No se pudo crear la especie'], 500);
    }

    /**
     * Importar fauna española
     */
    public function importSpanish(Request $request): RedirectResponse
    {
        $limit = $request->get('limit', 200);
        
        // Ejecutar en segundo plano si es posible
        Artisan::call('species:sync', [
            '--spanish' => true,
            '--limit' => $limit,
        ]);

        return redirect()->route('admin.species.index')
            ->with('success', "Importación de fauna española iniciada (hasta {$limit} especies).");
    }

    /**
     * Ver logs de sincronización
     */
    public function logs(): View
    {
        $recentErrors = Species::where('sync_status', 'error')
            ->orderBy('last_sync_attempt', 'desc')
            ->limit(50)
            ->get();

        $recentSynced = Species::where('sync_status', 'synced')
            ->orderBy('synced_at', 'desc')
            ->limit(50)
            ->get();

        return view('admin.species.logs', compact('recentErrors', 'recentSynced'));
    }

    /**
     * Exportar especies a CSV
     */
    public function export()
    {
        $species = Species::orderBy('scientific_name')->get();

        $filename = 'especies_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($species) {
            $file = fopen('php://output', 'w');
            
            // BOM para Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Cabeceras
            fputcsv($file, [
                'Nombre Científico',
                'Nombre Común',
                'Grupo Taxonómico',
                'Familia',
                'BOE',
                'IUCN',
                'CITES',
                'Protegida',
                'Última Sincronización',
            ], ';');

            foreach ($species as $sp) {
                fputcsv($file, [
                    $sp->scientific_name,
                    $sp->common_name,
                    $sp->taxon_group,
                    $sp->family,
                    $sp->boe_status,
                    $sp->iucn_category,
                    $sp->cites_appendix,
                    $sp->is_protected ? 'Sí' : 'No',
                    $sp->synced_at?->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
