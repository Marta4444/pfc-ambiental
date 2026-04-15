<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Report;
use App\Models\ReportCostItem;
use App\Services\CostCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ReportCostItemController extends Controller
{
    protected CostCalculationService $costService;

    public function __construct(CostCalculationService $costService)
    {
        $this->costService = $costService;
    }

    /**
     * Mostrar los costes de un report
     */
    public function index(Report $report): View
    {
        $costItems = $report->costItems()
            ->orderBy('group_key')
            ->orderByRaw("FIELD(cost_type, 'VR', 'VE', 'VS')")
            ->get();

        // Agrupar por group_key
        $groupedCosts = $costItems->groupBy('group_key');

        // Totales por tipo
        $totals = ReportCostItem::getTotalsByType($report->id);

        $categoryName = $report->category->name ?? '';
        $subcategoryName = $report->subcategory->name ?? '';

        return view('reports.costs.index', compact('report', 'groupedCosts', 'totals', 'categoryName', 'subcategoryName'));
    }

    /**
     * Calcular costes para un report usando el servicio
     */
    public function calculate(Report $report): RedirectResponse
    {
        // Bloquear si el caso está finalizado
        if ($report->isFinalizado()) {
            return redirect()->route('reports.show', $report)
                ->with('error', 'Este caso está finalizado y no se puede modificar. Contacta con un administrador para reabrirlo.');
        }

        // Verificar que hay detalles
        if (!$report->hasDetails()) {
            return redirect()->route('reports.show', $report)
                ->with('error', 'El caso no tiene detalles. Añade detalles antes de calcular costes.');
        }

        // Permitir solo al usuario asignado o admin
        $user = auth()->user();
        if (!$user || ($user->role !== 'admin' && $report->assigned_to !== $user->id)) {
            abort(403, 'No tienes permiso para calcular los costes de este caso.');
        }

        try {
            // Usar el servicio de cálculo de costes
            $results = $this->costService->calculateForReport($report);

            Log::info('Cálculo completado', [
                'report_id' => $report->id,
                'totals' => $results['totals'],
                'errors' => $results['errors'] ?? [],
            ]);

            // Verificar si hubo errores
            if (!empty($results['errors'])) {
                $errorMsg = implode('. ', $results['errors']);
                return redirect()->route('report-costs.index', $report)
                    ->with('warning', "Costes calculados con advertencias: {$errorMsg}");
            }

            $total = number_format($results['totals']['total'], 2, ',', '.');

            AuditHelper::logCalculateCosts($report, $results);

            return redirect()->route('report-costs.index', $report)
                ->with('success', "Costes calculados correctamente. Total: {$total} €");
        } catch (\Exception $e) {
            Log::error('Error al calcular costes', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('reports.show', $report)
                ->with('error', 'Error al calcular costes: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar costes de un report
     */
    public function destroy(Report $report): RedirectResponse
    {
        // Bloquear si el caso está finalizado
        if ($report->isFinalizado()) {
            return redirect()->route('reports.show', $report)
                ->with('error', 'Este caso está finalizado y no se puede modificar. Contacte con un administrador para reabrirlo.');
        }

        $report->costItems()->delete();
        
        $report->update([
            'vr_total' => 0,
            've_total' => 0,
            'vs_total' => 0,
            'total_cost' => 0,
        ]);

        AuditHelper::logResetCosts($report);

        return back()->with('success', 'Costes eliminados correctamente.');
    }
}