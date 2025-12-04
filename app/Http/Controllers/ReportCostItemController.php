<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportCostItem;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ReportCostItemController extends Controller
{
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

        return view('reports.costs.index', compact('report', 'groupedCosts', 'totals'));
    }

    /**
     * Calcular costes para un report (llamará al servicio)
     */
    public function calculate(Report $report): RedirectResponse
    {
        // Verificar que hay detalles
        if (!$report->hasDetails()) {
            return back()->with('error', 'El caso no tiene detalles. Añade detalles antes de calcular costes.');
        }

        // TODO: Implementar CostCalculationService
        // Por ahora, usamos una lógica básica similar al seeder

        try {
            // Eliminar costes anteriores
            $report->costItems()->delete();

            // Obtener grupos de detalles
            $groups = $report->details()
                ->select('group_key')
                ->distinct()
                ->pluck('group_key');

            foreach ($groups as $groupKey) {
                $this->calculateGroupCosts($report, $groupKey);
            }

            // Actualizar totales del report
            $report->updateTotalsFromCostItems();

            return back()->with('success', 'Costes calculados correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al calcular costes: ' . $e->getMessage());
        }
    }

    /**
     * Calcular costes para un grupo específico
     */
    protected function calculateGroupCosts(Report $report, string $groupKey): void
    {
        $groupDetails = $report->details()
            ->where('group_key', $groupKey)
            ->get()
            ->keyBy('field_key');

        $conceptName = $this->getConceptName($groupDetails, $groupKey);
        $cantidad = (int) ($groupDetails->get('cantidad')?->value ?? 1);

        // VR
        $vrBase = (float) ($groupDetails->get('coste_reposicion')?->value ?? rand(500, 5000));
        $crValue = $this->extractCrValue($groupDetails);
        
        ReportCostItem::create([
            'report_id' => $report->id,
            'group_key' => $groupKey,
            'cost_type' => 'VR',
            'concept_name' => $conceptName,
            'base_value' => $vrBase,
            'cr_value' => $crValue,
            'gi_value' => null,
            'total_cost' => $vrBase * $cantidad * ($crValue ?? 1),
            'coef_info_json' => ['tipo' => 'VR', 'fecha' => now()->toDateTimeString()],
        ]);

        // VE
        $veBase = (float) ($groupDetails->get('ve')?->value ?? rand(1000, 10000));
        $giValue = $this->calculateGravityIndex($groupDetails, $report);
        
        ReportCostItem::create([
            'report_id' => $report->id,
            'group_key' => $groupKey,
            'cost_type' => 'VE',
            'concept_name' => $conceptName,
            'base_value' => $veBase,
            'cr_value' => $crValue,
            'gi_value' => $giValue,
            'total_cost' => $veBase * $cantidad * $giValue,
            'coef_info_json' => ['tipo' => 'VE', 'fecha' => now()->toDateTimeString()],
        ]);

        // VS
        $vsBase = rand(200, 2000);
        
        ReportCostItem::create([
            'report_id' => $report->id,
            'group_key' => $groupKey,
            'cost_type' => 'VS',
            'concept_name' => $conceptName,
            'base_value' => $vsBase,
            'cr_value' => null,
            'gi_value' => null,
            'total_cost' => $vsBase * $cantidad,
            'coef_info_json' => ['tipo' => 'VS', 'fecha' => now()->toDateTimeString()],
        ]);
    }

    /**
     * Obtener nombre del concepto
     */
    protected function getConceptName($groupDetails, string $groupKey): string
    {
        if ($groupDetails->has('especie')) {
            return $groupDetails->get('especie')->value;
        }
        if ($groupDetails->has('tipo_residuo')) {
            return 'Residuo: ' . $groupDetails->get('tipo_residuo')->value;
        }
        return ucfirst(str_replace('_', ' ', $groupKey));
    }

    /**
     * Extraer valor CR
     */
    protected function extractCrValue($groupDetails): ?float
    {
        if ($groupDetails->has('estado_vital')) {
            $estado = strtolower($groupDetails->get('estado_vital')->value);
            return match ($estado) {
                'muerto' => 1.0,
                'herido', 'crítico' => 0.7,
                'vivo' => 0.3,
                default => 0.5,
            };
        }
        return 1.0;
    }

    /**
     * Calcular índice de gravedad
     */
    protected function calculateGravityIndex($groupDetails, Report $report): float
    {
        $gi = match ($report->urgency) {
            'urgente' => 1.5,
            'alta' => 1.3,
            default => 1.0,
        };

        if ($groupDetails->has('estado_vital')) {
            $estado = strtolower($groupDetails->get('estado_vital')->value);
            $gi *= match ($estado) {
                'muerto' => 1.5,
                'crítico' => 1.4,
                'herido' => 1.2,
                default => 1.0,
            };
        }

        return round($gi, 4);
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