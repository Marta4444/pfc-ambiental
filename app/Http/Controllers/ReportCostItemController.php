<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportCostItem;
use App\Services\CostCalculationService;
use Illuminate\Http\Request;
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

        // Obtener info de las fórmulas para la categoría
        $formulaInfo = CostCalculationService::getFormulaInfo($report->category->name ?? '');

        return view('reports.costs.index', compact('report', 'groupedCosts', 'totals', 'formulaInfo'));
    }

    /**
     * Calcular costes para un report usando el servicio
     */
    public function calculate(Report $report): RedirectResponse
    {
        \Log::info('Iniciando cálculo de costes', ['report_id' => $report->id]);

        // Verificar que hay detalles
        if (!$report->hasDetails()) {
            \Log::warning('El report no tiene detalles', ['report_id' => $report->id]);
            return redirect()->route('reports.show', $report)
                ->with('error', 'El caso no tiene detalles. Añade detalles antes de calcular costes.');
        }

        try {
            // Usar el servicio de cálculo de costes
            $results = $this->costService->calculateForReport($report);

            \Log::info('Cálculo completado', [
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
            return redirect()->route('report-costs.index', $report)
                ->with('success', "Costes calculados correctamente. Total: {$total} €");
        } catch (\Exception $e) {
            \Log::error('Error al calcular costes', [
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
        $report->costItems()->delete();
        
        $report->update([
            'vr_total' => 0,
            've_total' => 0,
            'vs_total' => 0,
            'total_cost' => 0,
        ]);

        return back()->with('success', 'Costes eliminados correctamente.');
    }
}