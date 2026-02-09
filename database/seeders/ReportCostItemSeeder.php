<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\ReportCostItem;
use App\Models\ReportDetail;
use Illuminate\Database\Seeder;

class ReportCostItemSeeder extends Seeder
{
    /**
     * Coeficientes de ejemplo para diferentes tipos
     */
    protected array $coeficientes = [
        'proteccion' => [
            'EN' => 2.0,      // En peligro
            'VU' => 1.5,      // Vulnerable
            'NT' => 1.2,      // Casi amenazada
            'LC' => 1.0,      // Preocupación menor
        ],
        'gravedad' => [
            'alta' => 1.5,
            'media' => 1.2,
            'baja' => 1.0,
        ],
        'area_protegida' => [
            'parque_nacional' => 2.0,
            'parque_natural' => 1.5,
            'reserva' => 1.3,
            'ninguna' => 1.0,
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener reports que tengan detalles
        $reportsWithDetails = Report::whereHas('details')->get();

        if ($reportsWithDetails->isEmpty()) {
            $this->command->warn('No hay reportes con detalles. Ejecuta primero ReportDetailSeeder.');
            return;
        }

        $reportsProcessed = 0;
        $costItemsCreated = 0;

        foreach ($reportsWithDetails as $report) {
            // Solo procesar ~70% de los reports con detalles
            if (rand(1, 100) > 70) {
                continue;
            }

            // Eliminar cost items existentes para este report
            $report->costItems()->delete();

            // Obtener grupos únicos de detalles
            $groups = $report->details()
                ->select('group_key')
                ->distinct()
                ->pluck('group_key');

            foreach ($groups as $groupKey) {
                $groupDetails = $report->details()
                    ->where('group_key', $groupKey)
                    ->get()
                    ->keyBy('field_key');

                // Obtener nombre del concepto (especie, tipo residuo, etc.)
                $conceptName = $this->getConceptName($groupDetails, $groupKey);

                // Generar costes VR, VE, VS para este grupo
                $costItemsCreated += $this->createCostItemsForGroup(
                    $report,
                    $groupKey,
                    $conceptName,
                    $groupDetails
                );
            }

            // Actualizar totales del report
            $report->updateTotalsFromCostItems();
            $reportsProcessed++;
        }

        $this->command->info("Se han procesado {$reportsProcessed} reportes y creado {$costItemsCreated} items de coste.");
    }

    /**
     * Crear items de coste para un grupo de detalles
     */
    protected function createCostItemsForGroup(
        Report $report,
        string $groupKey,
        string $conceptName,
        $groupDetails
    ): int {
        $itemsCreated = 0;

        // Obtener cantidad si existe
        $cantidad = (int) ($groupDetails->get('cantidad')?->value ?? 1);
        
        // Obtener CR si existe
        $crValue = $this->extractCrValue($groupDetails);
        
        // Calcular índice de gravedad
        $giValue = $this->calculateGravityIndex($groupDetails, $report);

        // === VR (Valor de Reposición) ===
        $vrBase = $this->calculateVrBase($groupDetails, $groupKey);
        if ($vrBase > 0) {
            $vrCoefs = $this->getCoefficientsInfo('VR', $groupDetails);
            $vrTotal = $vrBase * $cantidad * ($crValue ?? 1);

            ReportCostItem::create([
                'report_id' => $report->id,
                'group_key' => $groupKey,
                'cost_type' => 'VR',
                'concept_name' => $conceptName,
                'base_value' => $vrBase,
                'cr_value' => $crValue,
                'gi_value' => null,
                'total_cost' => $vrTotal,
                'coef_info_json' => $vrCoefs,
            ]);
            $itemsCreated++;
        }

        // === VE (Valor del recurso extraido) ===
        $veBase = $this->calculateVeBase($groupDetails, $groupKey);
        if ($veBase > 0) {
            $veCoefs = $this->getCoefficientsInfo('VE', $groupDetails);
            $veTotal = $veBase * $cantidad * $giValue;

            ReportCostItem::create([
                'report_id' => $report->id,
                'group_key' => $groupKey,
                'cost_type' => 'VE',
                'concept_name' => $conceptName,
                'base_value' => $veBase,
                'cr_value' => $crValue,
                'gi_value' => $giValue,
                'total_cost' => $veTotal,
                'coef_info_json' => $veCoefs,
            ]);
            $itemsCreated++;
        }

        // === VS (Valor ecosistémico) ===
        $vsBase = $this->calculateVsBase($groupDetails, $groupKey);
        if ($vsBase > 0) {
            $vsCoefs = $this->getCoefficientsInfo('VS', $groupDetails);
            $vsTotal = $vsBase * $cantidad;

            ReportCostItem::create([
                'report_id' => $report->id,
                'group_key' => $groupKey,
                'cost_type' => 'VS',
                'concept_name' => $conceptName,
                'base_value' => $vsBase,
                'cr_value' => null,
                'gi_value' => null,
                'total_cost' => $vsTotal,
                'coef_info_json' => $vsCoefs,
            ]);
            $itemsCreated++;
        }

        return $itemsCreated;
    }

    /**
     * Obtener nombre del concepto desde los detalles
     */
    protected function getConceptName($groupDetails, string $groupKey): string
    {
        // Prioridad: especie > tipo_residuo > tipo_gas > origen_agua > group_key
        if ($groupDetails->has('especie')) {
            return $groupDetails->get('especie')->value;
        }
        if ($groupDetails->has('tipo_residuo')) {
            return 'Residuo: ' . $groupDetails->get('tipo_residuo')->value;
        }
        if ($groupDetails->has('tipo_gas')) {
            return 'Emisión: ' . $groupDetails->get('tipo_gas')->value;
        }
        if ($groupDetails->has('origen_agua')) {
            return 'Agua: ' . $groupDetails->get('origen_agua')->value;
        }

        return ucfirst(str_replace('_', ' ', $groupKey));
    }

    /**
     * Extraer valor CR de los detalles
     */
    protected function extractCrValue($groupDetails): ?float
    {
        // Buscar campo CR o coste_reposicion
        if ($groupDetails->has('cr') && is_numeric($groupDetails->get('cr')->value)) {
            return (float) $groupDetails->get('cr')->value;
        }
        
        // Simular un CR basado en estado vital si existe
        if ($groupDetails->has('estado_vital')) {
            $estado = strtolower($groupDetails->get('estado_vital')->value);
            return match ($estado) {
                'muerto' => 1.0,
                'herido', 'crítico' => 0.7,
                'vivo' => 0.3,
                default => 0.5,
            };
        }

        return fake()->randomFloat(2, 0.5, 1.5);
    }

    /**
     * Calcular índice de gravedad
     */
    protected function calculateGravityIndex($groupDetails, Report $report): float
    {
        $gi = 1.0;

        // Factor por urgencia del caso
        $gi *= match ($report->urgency) {
            'urgente' => 1.5,
            'alta' => 1.3,
            default => 1.0,
        };

        // Factor por estado vital si existe
        if ($groupDetails->has('estado_vital')) {
            $estado = strtolower($groupDetails->get('estado_vital')->value);
            $gi *= match ($estado) {
                'muerto' => 1.5,
                'crítico' => 1.4,
                'herido' => 1.2,
                default => 1.0,
            };
        }

        // Factor por método de captura si existe
        if ($groupDetails->has('metodo_captura')) {
            $metodo = strtolower($groupDetails->get('metodo_captura')->value);
            if (in_array($metodo, ['veneno', 'cepo', 'trampa'])) {
                $gi *= 1.3;
            }
        }

        return round($gi, 4);
    }

    /**
     * Calcular valor base VR
     */
    protected function calculateVrBase($groupDetails, string $groupKey): float
    {
        // Si hay coste_reposicion explícito
        if ($groupDetails->has('coste_reposicion')) {
            return (float) $groupDetails->get('coste_reposicion')->value;
        }

        // Valores base según tipo de grupo
        $prefix = explode('_', $groupKey)[0];
        
        return match ($prefix) {
            'species' => fake()->randomFloat(2, 500, 15000),
            'residue' => fake()->randomFloat(2, 100, 5000),
            'emission' => fake()->randomFloat(2, 200, 3000),
            'water' => fake()->randomFloat(2, 150, 4000),
            'eei' => fake()->randomFloat(2, 300, 8000),
            default => fake()->randomFloat(2, 100, 2000),
        };
    }

    /**
     * Calcular valor base VE
     */
    protected function calculateVeBase($groupDetails, string $groupKey): float
    {
        // Si hay VE explícito
        if ($groupDetails->has('ve')) {
            return (float) $groupDetails->get('ve')->value;
        }

        $prefix = explode('_', $groupKey)[0];
        
        return match ($prefix) {
            'species' => fake()->randomFloat(2, 1000, 30000),
            'residue' => fake()->randomFloat(2, 500, 10000),
            'emission' => fake()->randomFloat(2, 800, 15000),
            'water' => fake()->randomFloat(2, 600, 12000),
            'eei' => fake()->randomFloat(2, 2000, 25000),
            default => fake()->randomFloat(2, 500, 8000),
        };
    }

    /**
     * Calcular valor base VS
     */
    protected function calculateVsBase($groupDetails, string $groupKey): float
    {
        $prefix = explode('_', $groupKey)[0];
        
        // VS generalmente es un porcentaje del VE o un valor fijo
        return match ($prefix) {
            'species' => fake()->randomFloat(2, 200, 5000),
            'residue' => fake()->randomFloat(2, 100, 2000),
            'emission' => fake()->randomFloat(2, 150, 3000),
            'water' => fake()->randomFloat(2, 100, 2500),
            'eei' => fake()->randomFloat(2, 500, 6000),
            default => fake()->randomFloat(2, 100, 1500),
        };
    }

    /**
     * Obtener info de coeficientes aplicados
     */
    protected function getCoefficientsInfo(string $costType, $groupDetails): array
    {
        $info = [
            'tipo' => $costType,
            'fecha_calculo' => now()->toDateTimeString(),
            'coeficientes' => [],
        ];

        // Añadir coeficientes según el tipo
        if ($costType === 'VR' && $groupDetails->has('estado_vital')) {
            $info['coeficientes']['estado_vital'] = [
                'valor' => $groupDetails->get('estado_vital')->value,
                'factor' => $this->extractCrValue($groupDetails),
            ];
        }

        if ($costType === 'VE') {
            $info['coeficientes']['gravedad'] = [
                'descripcion' => 'Índice calculado según urgencia y circunstancias',
            ];
        }

        if ($groupDetails->has('cantidad')) {
            $info['coeficientes']['cantidad'] = [
                'valor' => (int) $groupDetails->get('cantidad')->value,
                'descripcion' => 'Número de unidades afectadas',
            ];
        }

        return $info;
    }
}