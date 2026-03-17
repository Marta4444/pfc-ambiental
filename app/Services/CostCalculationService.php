<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportCostItem;
use App\Models\ReportDetail;
use App\Models\Species;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para el cálculo de costes ambientales.
 * 
 * Fórmula general: VDT (Valor del Daño Total) = VE + VR + VS
 * 
 * Este servicio es escalable para diferentes categorías con diferentes fórmulas.
 */
class CostCalculationService
{
    /**
     * Constantes para el cálculo
     */
    private const COSTE_BASE = 300.00; // CB - Coste Base en euros

    /**
     * Multiplicadores IUCN para situación legal (L)
     */
    private const IUCN_MULTIPLIERS = [
        'CR' => 70,    // En peligro crítico
        'EN' => 60,    // En peligro
        'VU' => 40,    // Vulnerable
        'NT' => 20,    // Casi amenazado
        'LC' => 6.5,   // Preocupación menor
        'DD' => 5,     // Datos insuficientes
        'NE' => 5,     // No evaluado
    ];

    /**
     * Multiplicadores CITES (N)
     */
    private const CITES_MULTIPLIERS = [
        'I'   => 3,
        'II'  => 2,
        'III' => 1.5,
    ];

    /**
     * Multiplicadores de Madurez (B)
     * Mapeo de valores del formulario a multiplicadores
     */
    private const MADUREZ_MULTIPLIERS = [
        'Maduro'      => 1.5,
        'Adulto'      => 1.5,
        'Senil'       => 1.5,
        'Inmaduro'    => 1.1,
        'Juvenil'     => 1.1,
        'Subadulto'   => 1.1,
        'Desconocido' => 1.1, // Default conservador
    ];

    /**
     * Multiplicadores de Subcategoría (S)
     * Factor según el tipo de hecho ilícito en biodiversidad
     */
    private const SUBCATEGORY_MULTIPLIERS = [
        'Comercio'                      => 1,
        'Caza furtiva'                  => 2,
        'Especies cinegéticas'          => 2,
        'Endemismos'                    => 2,
        'Especie Exótica Invasora (EEI)' => 1.5,
    ];

    /**
     * Multiplicadores de Origen del Agua (T)
     * Factor ecosistémico según la procedencia del agua extraída
     */
    private const ORIGEN_AGUA_MULTIPLIERS = [
        'Superficial'  => 1.8,  // Alto impacto - ríos, lagos visibles
        'Subterránea'  => 1.6,  // Impacto moderado-alto - acuíferos
        'Pozo'         => 1.5,  // Similar a subterránea pero localizado
        'Manantial'    => 2.0,  // Muy sensible - nacimientos naturales
        'Red pública'  => 1.2,  // Menor impacto ecosistémico directo
        'Otros'        => 1.4,  // Valor intermedio por defecto
    ];

    /**
     * Valores estándar para Índice de Gravedad (IG)
     * Cada dimensión pondera 25% del total
     */
    private const IG_UBICACION = [
        'Espacio no protegido'        => 50,
        'Espacio cercano a protegido' => 75,
        'Espacio protegido'           => 100,
    ];

    private const IG_NIVEL_TROFICO = [
        'Primario (herbívoros y granívoros)'  => 50,
        'Secundario (insectívoro y omnívoro)' => 75,
        'Terciario (carnívoro)'               => 100,
    ];

    private const IG_REPRODUCCION_CAUTIVERIO = [
        'Criada comercialmente'        => 50,
        'Reproducida en cautiverio'    => 75,
        'No reproducida en cautiverio' => 100,
    ];

    /**
     * Mapeo de estado_vital del formulario a valores para IG
     */
    private const IG_ESTADO_VITAL = [
        'Sano'        => 50,
        'Vivo'        => 50,
        'Herido'      => 75,
        'Crítico'     => 75,
        'Muerto'      => 100,
        'Desconocido' => 75, // Valor intermedio como default
    ];

    /**
     * Calcular todos los costes para un Report
     */
    public function calculateForReport(Report $report): array
    {
        // Obtener la categoría para determinar qué fórmula usar
        $categoryName = $report->category->name ?? '';

        // Seleccionar la estrategia de cálculo según la categoría
        return match ($categoryName) {
            'Biodiversidad' => $this->calculateBiodiversidad($report),
            'Infraestructuras' => $this->calculateInfraestructuras($report),
            'Vertidos' => $this->calculateVertidos($report),
            default => $this->calculateGeneric($report),
        };
    }

    /**
     * Calcular costes para categoría BIODIVERSIDAD
     * 
     * Fórmula VR = [(CB * L * N * B * S) * q] + CR
     * Fórmula VS = VR * IG
     * VE = introducido manualmente
     * 
     * Donde S es el multiplicador de subcategoría:
     * - Comercio: S = 1
     * - Caza furtiva, Especies cinegéticas, Endemismos: S = 2
     * - Especie Exótica Invasora (EEI): S = 1.5
     */
    public function calculateBiodiversidad(Report $report): array
    {
        $results = [
            'items' => [],
            'totals' => [
                'VR' => 0,
                'VE' => 0,
                'VS' => 0,
                'total' => 0,
            ],
            'errors' => [],
        ];

        // Eliminar costes anteriores
        $report->costItems()->delete();

        // Obtener grupos de detalles (species_1, species_2, etc.)
        $groups = $report->details()
            ->select('group_key')
            ->distinct()
            ->pluck('group_key');

        foreach ($groups as $groupKey) {
            try {
                $groupResult = $this->calculateBiodiversidadGroup($report, $groupKey);
                $results['items'][$groupKey] = $groupResult;
                
                $results['totals']['VR'] += $groupResult['VR']['total_cost'] ?? 0;
                $results['totals']['VE'] += $groupResult['VE']['total_cost'] ?? 0;
                $results['totals']['VS'] += $groupResult['VS']['total_cost'] ?? 0;
            } catch (\Exception $e) {
                $results['errors'][] = "Error en grupo {$groupKey}: " . $e->getMessage();
                Log::error("Error calculando costes para grupo {$groupKey}", [
                    'report_id' => $report->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $results['totals']['total'] = $results['totals']['VR'] + $results['totals']['VE'] + $results['totals']['VS'];

        // Actualizar totales en el report
        $report->update([
            'vr_total' => $results['totals']['VR'],
            've_total' => $results['totals']['VE'],
            'vs_total' => $results['totals']['VS'],
            'total_cost' => $results['totals']['total'],
        ]);

        return $results;
    }

    /**
     * Calcular costes para un grupo específico de Biodiversidad
     */
    protected function calculateBiodiversidadGroup(Report $report, string $groupKey): array
    {
        // Obtener todos los detalles del grupo
        $details = $report->details()
            ->where('group_key', $groupKey)
            ->get()
            ->keyBy('field_key');

        // Extraer valores de los campos
        $especieDetail = $details->get('especie');
        $especie = $especieDetail?->value ?? 'Especie desconocida';
        $speciesId = $especieDetail?->species_id;

        // Cargar datos de la especie desde la BD para obtener IUCN y CITES
        $speciesData = null;
        $iucnCategory = '';
        $citesAppendix = '';
        
        if ($speciesId) {
            $speciesData = Species::find($speciesId);
        }
        
        // Si no tenemos species_id, buscar por nombre
        if (!$speciesData && $especie !== 'Especie desconocida') {
            $speciesData = Species::where('scientific_name', $especie)
                ->orWhere('common_name', $especie)
                ->first();
        }

        // Si encontramos la especie, usar sus datos
        if ($speciesData) {
            $iucnCategory = strtoupper(trim($speciesData->iucn_category ?? ''));
            $citesAppendix = strtoupper(trim($speciesData->cites_appendix ?? ''));
            // Usar nombre científico como concepto si está disponible
            if ($especie === 'Especie desconocida') {
                $especie = $speciesData->scientific_name ?? $speciesData->common_name ?? 'Especie desconocida';
            }
        }
        
        // Si hay valores en los detalles, usarlos (prioridad al usuario)
        if ($details->get('iucn_category')?->value) {
            $iucnCategory = strtoupper(trim($details->get('iucn_category')->value));
        }
        if ($details->get('cites_appendix')?->value) {
            $citesAppendix = strtoupper(trim($details->get('cites_appendix')->value));
        }

        $madurez = $details->get('madurez')?->value ?? 'Desconocido';
        $cantidad = (int) ($details->get('cantidad')?->value ?? 1);
        $costeReposicion = (float) ($details->get('coste_reposicion')?->value ?? 0);
        $veManual = (float) ($details->get('ve')?->value ?? 0);
        
        // Campos para el Índice de Gravedad
        $ubicacionProteccion = $details->get('ubicacion_proteccion')?->value ?? 'Espacio no protegido';
        $nivelTrofico = $details->get('nivel_trofico')?->value ?? 'Secundario (insectívoro y omnívoro)';
        $reproduccionCautiverio = $details->get('reproduccion_cautiverio')?->value ?? 'No reproducida en cautiverio';
        $estadoVital = $details->get('estado_vital')?->value ?? 'Desconocido';

        // Calcular multiplicadores
        $L = self::IUCN_MULTIPLIERS[$iucnCategory] ?? 5; // Default a DD si no se encuentra
        $N = self::CITES_MULTIPLIERS[$citesAppendix] ?? 1; // Default a 1 si no está en CITES
        $B = self::MADUREZ_MULTIPLIERS[$madurez] ?? 1.1; // Default a Inmaduro

        // Obtener el multiplicador de Subcategoría (S)
        $subcategoryName = $report->subcategory->name ?? '';
        $S = self::SUBCATEGORY_MULTIPLIERS[$subcategoryName] ?? 1; // Default a 1 si no se encuentra

        // Log para debug
        Log::debug("Calculando costes para {$groupKey}", [
            'especie' => $especie,
            'species_id' => $speciesId,
            'iucn' => $iucnCategory,
            'cites' => $citesAppendix,
            'madurez' => $madurez,
            'cantidad' => $cantidad,
            'L' => $L,
            'N' => $N,
            'B' => $B,
            'S' => $S,
            'subcategoria' => $subcategoryName,
        ]);

        // Calcular VR = [(CB * L * N * B * S) * q] + CR
        $vrBase = self::COSTE_BASE * $L * $N * $B * $S;
        $vrTotal = ($vrBase * $cantidad) + $costeReposicion;

        // Calcular Índice de Gravedad (IG)
        $ig = $this->calculateIndiceGravedad(
            $ubicacionProteccion,
            $nivelTrofico,
            $reproduccionCautiverio,
            $estadoVital
        );

        // Calcular VS = VR * IG
        $vsTotal = $vrTotal * $ig;

        // VE es introducido manualmente
        $veTotal = $veManual;

        // Crear items de coste en la base de datos
        $vrItem = ReportCostItem::create([
            'report_id' => $report->id,
            'group_key' => $groupKey,
            'cost_type' => 'VR',
            'concept_name' => $especie,
            'base_value' => $vrBase,
            'cr_value' => $costeReposicion,
            'gi_value' => null,
            'total_cost' => $vrTotal,
            'coef_info_json' => [
                'formula' => 'VR = [(CB * L * N * B * S) * q] + CR',
                'CB' => self::COSTE_BASE,
                'L' => $L,
                'L_source' => "IUCN: {$iucnCategory}",
                'N' => $N,
                'N_source' => "CITES: " . ($citesAppendix ?: 'No'),
                'B' => $B,
                'B_source' => "Madurez: {$madurez}",
                'S' => $S,
                'S_source' => "Subcategoría: {$subcategoryName}",
                'q' => $cantidad,
                'CR' => $costeReposicion,
            ],
        ]);

        $veItem = ReportCostItem::create([
            'report_id' => $report->id,
            'group_key' => $groupKey,
            'cost_type' => 'VE',
            'concept_name' => $especie,
            'base_value' => $veTotal,
            'cr_value' => null,
            'gi_value' => null,
            'total_cost' => $veTotal,
            'coef_info_json' => [
                'formula' => 'VE = valor introducido manualmente',
                'valor_manual' => $veManual,
            ],
        ]);

        $vsItem = ReportCostItem::create([
            'report_id' => $report->id,
            'group_key' => $groupKey,
            'cost_type' => 'VS',
            'concept_name' => $especie,
            'base_value' => $vrTotal,
            'cr_value' => null,
            'gi_value' => $ig,
            'total_cost' => $vsTotal,
            'coef_info_json' => [
                'formula' => 'VS = VR * IG',
                'VR' => $vrTotal,
                'IG' => $ig,
                'IG_components' => [
                    'ubicacion' => [
                        'valor' => $ubicacionProteccion,
                        'puntuacion' => self::IG_UBICACION[$ubicacionProteccion] ?? 50,
                        'ponderacion' => 0.25,
                    ],
                    'nivel_trofico' => [
                        'valor' => $nivelTrofico,
                        'puntuacion' => self::IG_NIVEL_TROFICO[$nivelTrofico] ?? 50,
                        'ponderacion' => 0.25,
                    ],
                    'reproduccion_cautiverio' => [
                        'valor' => $reproduccionCautiverio,
                        'puntuacion' => self::IG_REPRODUCCION_CAUTIVERIO[$reproduccionCautiverio] ?? 100,
                        'ponderacion' => 0.25,
                    ],
                    'estado_vital' => [
                        'valor' => $estadoVital,
                        'puntuacion' => self::IG_ESTADO_VITAL[$estadoVital] ?? 50,
                        'ponderacion' => 0.25,
                    ],
                ],
            ],
        ]);

        return [
            'VR' => [
                'base_value' => $vrBase,
                'total_cost' => $vrTotal,
                'item' => $vrItem,
            ],
            'VE' => [
                'base_value' => $veTotal,
                'total_cost' => $veTotal,
                'item' => $veItem,
            ],
            'VS' => [
                'base_value' => $vrTotal,
                'total_cost' => $vsTotal,
                'item' => $vsItem,
            ],
            'IG' => $ig,
            'cantidad' => $cantidad,
            'especie' => $especie,
        ];
    }

    /**
     * Calcular el Índice de Gravedad (IG)
     * 
     * IG = suma de (puntuación * ponderación) / 100
     * Cada dimensión tiene ponderación de 25%
     */
    protected function calculateIndiceGravedad(
        string $ubicacion,
        string $nivelTrofico,
        string $reproduccionCautiverio,
        string $estadoVital
    ): float {
        $ponderacion = 0.25; // 25% cada dimensión

        // Obtener puntuaciones (valores estándar: 50, 75 o 100)
        $puntuacionUbicacion = self::IG_UBICACION[$ubicacion] ?? 50;
        $puntuacionTrofico = self::IG_NIVEL_TROFICO[$nivelTrofico] ?? 50;
        $puntuacionReproduccion = self::IG_REPRODUCCION_CAUTIVERIO[$reproduccionCautiverio] ?? 100;
        $puntuacionEstadoVital = self::IG_ESTADO_VITAL[$estadoVital] ?? 50;

        // Sumar contribuciones ponderadas
        $igPorcentaje = 
            ($puntuacionUbicacion * $ponderacion) +
            ($puntuacionTrofico * $ponderacion) +
            ($puntuacionReproduccion * $ponderacion) +
            ($puntuacionEstadoVital * $ponderacion);

        // Convertir a tanto por 1
        return round($igPorcentaje / 100, 4);
    }

    /**
     * Calcular costes para categoría INFRAESTRUCTURAS
     * 
     * Subcategorías soportadas:
     * - Extracciones de aguas: VE = volumen * precio_unitario, VR = manual, VS = VS_manual * T
     * - Parques eólicos: fórmula genérica (pendiente de implementar)
     */
    public function calculateInfraestructuras(Report $report): array
    {
        $subcategoryName = $report->subcategory->name ?? '';

        return match ($subcategoryName) {
            'Extracciones de aguas' => $this->calculateExtraccionAguas($report),
            default => $this->calculateGeneric($report),
        };
    }

    /**
     * Calcular costes para Extracción de Aguas
     * 
     * Fórmulas:
     * - VE = Volumen × Precio Unitario
     * - VR = Valor introducido manualmente
     * - VS = VS_manual × T (donde T depende del origen del agua)
     */
    protected function calculateExtraccionAguas(Report $report): array
    {
        $results = [
            'items' => [],
            'totals' => [
                'VR' => 0,
                'VE' => 0,
                'VS' => 0,
                'total' => 0,
            ],
            'errors' => [],
        ];

        // Eliminar costes anteriores
        $report->costItems()->delete();

        // Obtener grupos de detalles (agua_1, agua_2, etc.)
        $groups = $report->details()
            ->select('group_key')
            ->distinct()
            ->pluck('group_key');

        foreach ($groups as $groupKey) {
            try {
                $groupResult = $this->calculateExtraccionAguasGroup($report, $groupKey);
                $results['items'][$groupKey] = $groupResult;
                
                $results['totals']['VR'] += $groupResult['VR']['total_cost'] ?? 0;
                $results['totals']['VE'] += $groupResult['VE']['total_cost'] ?? 0;
                $results['totals']['VS'] += $groupResult['VS']['total_cost'] ?? 0;
            } catch (\Exception $e) {
                $results['errors'][] = "Error en grupo {$groupKey}: " . $e->getMessage();
                Log::error("Error calculando costes de agua para grupo {$groupKey}", [
                    'report_id' => $report->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $results['totals']['total'] = $results['totals']['VR'] + $results['totals']['VE'] + $results['totals']['VS'];

        // Actualizar totales en el report
        $report->update([
            'vr_total' => $results['totals']['VR'],
            've_total' => $results['totals']['VE'],
            'vs_total' => $results['totals']['VS'],
            'total_cost' => $results['totals']['total'],
        ]);

        return $results;
    }

    /**
     * Calcular costes para un grupo específico de Extracción de Aguas
     */
    protected function calculateExtraccionAguasGroup(Report $report, string $groupKey): array
    {
        // Obtener todos los detalles del grupo
        $details = $report->details()
            ->where('group_key', $groupKey)
            ->get()
            ->keyBy('field_key');

        // Extraer valores de los campos
        $volumen = (float) ($details->get('volumen')?->value ?? 0);
        $origenAgua = $details->get('origen_agua')?->value ?? 'Otros';
        $caudal = (float) ($details->get('caudal')?->value ?? 0);
        $precioUnitario = (float) ($details->get('precio_unitario')?->value ?? 0);
        $vrManual = (float) ($details->get('vr_manual')?->value ?? 0);
        $vsManual = (float) ($details->get('vs_manual')?->value ?? 0);

        // Obtener el multiplicador T según origen del agua
        $T = self::ORIGEN_AGUA_MULTIPLIERS[$origenAgua] ?? 1.4;

        // Concepto para mostrar
        $conceptName = "Agua ({$origenAgua}) - {$volumen} m³";

        // Log para debug
        Log::debug("Calculando costes de extracción de agua para {$groupKey}", [
            'volumen' => $volumen,
            'origen' => $origenAgua,
            'precio_unitario' => $precioUnitario,
            'vr_manual' => $vrManual,
            'vs_manual' => $vsManual,
            'T' => $T,
        ]);

        // Calcular VE = Volumen × Precio Unitario
        $veTotal = $volumen * $precioUnitario;

        // VR = Valor introducido manualmente
        $vrTotal = $vrManual;

        // VS = VS_manual × T
        $vsTotal = $vsManual * $T;

        // Crear items de coste en la base de datos
        $veItem = ReportCostItem::create([
            'report_id' => $report->id,
            'group_key' => $groupKey,
            'cost_type' => 'VE',
            'concept_name' => $conceptName,
            'base_value' => $volumen,
            'cr_value' => $precioUnitario,
            'gi_value' => null,
            'total_cost' => $veTotal,
            'coef_info_json' => [
                'formula' => 'VE = Volumen × Precio Unitario',
                'categoria' => 'Infraestructuras',
                'subcategoria' => 'Extracciones de aguas',
                'volumen' => $volumen,
                'volumen_unidad' => 'm³',
                'precio_unitario' => $precioUnitario,
                'precio_unitario_unidad' => '€/m³',
                'origen_agua' => $origenAgua,
            ],
        ]);

        $vrItem = ReportCostItem::create([
            'report_id' => $report->id,
            'group_key' => $groupKey,
            'cost_type' => 'VR',
            'concept_name' => $conceptName,
            'base_value' => $vrManual,
            'cr_value' => null,
            'gi_value' => null,
            'total_cost' => $vrTotal,
            'coef_info_json' => [
                'formula' => 'VR = Valor introducido manualmente',
                'categoria' => 'Infraestructuras',
                'subcategoria' => 'Extracciones de aguas',
                'valor_manual' => $vrManual,
                'origen_agua' => $origenAgua,
            ],
        ]);

        $vsItem = ReportCostItem::create([
            'report_id' => $report->id,
            'group_key' => $groupKey,
            'cost_type' => 'VS',
            'concept_name' => $conceptName,
            'base_value' => $vsManual,
            'cr_value' => null,
            'gi_value' => $T,
            'total_cost' => $vsTotal,
            'coef_info_json' => [
                'formula' => 'VS = VS_base × T',
                'categoria' => 'Infraestructuras',
                'subcategoria' => 'Extracciones de aguas',
                'vs_base' => $vsManual,
                'T' => $T,
                'T_source' => "Origen del agua: {$origenAgua}",
                'origen_agua' => $origenAgua,
                'T_explicacion' => $this->getExplicacionCoeficienteT($origenAgua),
            ],
        ]);

        return [
            'VE' => [
                'base_value' => $volumen,
                'total_cost' => $veTotal,
                'item' => $veItem,
            ],
            'VR' => [
                'base_value' => $vrManual,
                'total_cost' => $vrTotal,
                'item' => $vrItem,
            ],
            'VS' => [
                'base_value' => $vsManual,
                'total_cost' => $vsTotal,
                'item' => $vsItem,
            ],
            'T' => $T,
            'volumen' => $volumen,
            'origen_agua' => $origenAgua,
        ];
    }

    /**
     * Obtener explicación del coeficiente T según origen del agua
     */
    protected function getExplicacionCoeficienteT(string $origenAgua): string
    {
        return match ($origenAgua) {
            'Superficial' => 'Agua de ríos o lagos. Alto impacto ambiental por afectación directa a ecosistemas acuáticos.',
            'Subterránea' => 'Agua de acuíferos. Impacto moderado-alto por afectación a reservas hídricas subterráneas.',
            'Pozo' => 'Extracción de pozo. Similar a subterránea pero con impacto más localizado.',
            'Manantial' => 'Agua de manantial o nacimiento. Muy sensible por afectación a ecosistemas de nacimiento.',
            'Red pública' => 'Agua de red pública. Menor impacto ecosistémico directo.',
            default => 'Origen no especificado. Se aplica valor intermedio.',
        };
    }

    /**
     * Calcular costes para categoría VERTIDOS
     * 
     * Subcategorías soportadas:
     * - Vertido de aguas: VE = volumen * coste_limpieza_agua, VR = manual, VS = manual
     * - Vertido de residuos: fórmula genérica (pendiente)
     * - Vertido de suelos: fórmula genérica (pendiente)
     * - Emisiones atmosféricas: fórmula genérica (pendiente)
     */
    public function calculateVertidos(Report $report): array
    {
        $subcategoryName = $report->subcategory->name ?? '';

        return match ($subcategoryName) {
            'Vertido de aguas' => $this->calculateVertidoAguas($report),
            default => $this->calculateGeneric($report),
        };
    }

    /**
     * Calcular costes para Vertido de Aguas
     * 
     * Fórmulas:
     * - VE = Volumen × Coste de Limpieza del Agua (€/m³)
     * - VR = Valor introducido manualmente
     * - VS = Valor introducido manualmente
     * - Total = VE + VR + VS
     */
    protected function calculateVertidoAguas(Report $report): array
    {
        $results = [
            'items' => [],
            'totals' => [
                'VR' => 0,
                'VE' => 0,
                'VS' => 0,
                'total' => 0,
            ],
            'errors' => [],
        ];

        // Eliminar costes anteriores
        $report->costItems()->delete();

        // Obtener grupos de detalles (vertido_1, vertido_2, etc.)
        $groups = $report->details()
            ->select('group_key')
            ->distinct()
            ->pluck('group_key');

        foreach ($groups as $groupKey) {
            try {
                $groupResult = $this->calculateVertidoAguasGroup($report, $groupKey);
                $results['items'][$groupKey] = $groupResult;
                
                $results['totals']['VR'] += $groupResult['VR']['total_cost'] ?? 0;
                $results['totals']['VE'] += $groupResult['VE']['total_cost'] ?? 0;
                $results['totals']['VS'] += $groupResult['VS']['total_cost'] ?? 0;
            } catch (\Exception $e) {
                $results['errors'][] = "Error en grupo {$groupKey}: " . $e->getMessage();
                Log::error("Error calculando costes de vertido para grupo {$groupKey}", [
                    'report_id' => $report->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $results['totals']['total'] = $results['totals']['VR'] + $results['totals']['VE'] + $results['totals']['VS'];

        // Actualizar totales en el report
        $report->update([
            'vr_total' => $results['totals']['VR'],
            've_total' => $results['totals']['VE'],
            'vs_total' => $results['totals']['VS'],
            'total_cost' => $results['totals']['total'],
        ]);

        return $results;
    }

    /**
     * Calcular costes para un grupo específico de Vertido de Aguas
     */
    protected function calculateVertidoAguasGroup(Report $report, string $groupKey): array
    {
        // Obtener todos los detalles del grupo
        $details = $report->details()
            ->where('group_key', $groupKey)
            ->get()
            ->keyBy('field_key');

        // Extraer valores de los campos
        $volumen = (float) ($details->get('volumen')?->value ?? 0);
        $costeLimpieza = (float) ($details->get('coste_limpieza_agua')?->value ?? 0);
        $vrManual = (float) ($details->get('vr_manual')?->value ?? 0);
        $vsManual = (float) ($details->get('vs_manual')?->value ?? 0);

        // Concepto para mostrar
        $conceptName = "Vertido de agua - {$volumen} m³";

        // Log para debug
        Log::debug("Calculando costes de vertido de agua para {$groupKey}", [
            'volumen' => $volumen,
            'coste_limpieza' => $costeLimpieza,
            'vr_manual' => $vrManual,
            'vs_manual' => $vsManual,
        ]);

        // Calcular VE = Volumen × Coste de Limpieza
        $veTotal = $volumen * $costeLimpieza;

        // VR = Valor introducido manualmente
        $vrTotal = $vrManual;

        // VS = Valor introducido manualmente
        $vsTotal = $vsManual;

        // Crear items de coste en la base de datos
        $veItem = ReportCostItem::create([
            'report_id' => $report->id,
            'group_key' => $groupKey,
            'cost_type' => 'VE',
            'concept_name' => $conceptName,
            'base_value' => $volumen,
            'cr_value' => $costeLimpieza,
            'gi_value' => null,
            'total_cost' => $veTotal,
            'coef_info_json' => [
                'formula' => 'VE = Volumen × Coste Limpieza',
                'categoria' => 'Vertidos',
                'subcategoria' => 'Vertido de aguas',
                'volumen' => $volumen,
                'volumen_unidad' => 'm³',
                'coste_limpieza' => $costeLimpieza,
                'coste_limpieza_unidad' => '€/m³',
            ],
        ]);

        $vrItem = ReportCostItem::create([
            'report_id' => $report->id,
            'group_key' => $groupKey,
            'cost_type' => 'VR',
            'concept_name' => $conceptName,
            'base_value' => $vrManual,
            'cr_value' => null,
            'gi_value' => null,
            'total_cost' => $vrTotal,
            'coef_info_json' => [
                'formula' => 'VR = Valor introducido manualmente',
                'categoria' => 'Vertidos',
                'subcategoria' => 'Vertido de aguas',
                'valor_manual' => $vrManual,
            ],
        ]);

        $vsItem = ReportCostItem::create([
            'report_id' => $report->id,
            'group_key' => $groupKey,
            'cost_type' => 'VS',
            'concept_name' => $conceptName,
            'base_value' => $vsManual,
            'cr_value' => null,
            'gi_value' => null,
            'total_cost' => $vsTotal,
            'coef_info_json' => [
                'formula' => 'VS = Valor introducido manualmente',
                'categoria' => 'Vertidos',
                'subcategoria' => 'Vertido de aguas',
                'valor_manual' => $vsManual,
            ],
        ]);

        return [
            'VE' => [
                'item' => $veItem,
                'total_cost' => $veTotal,
            ],
            'VR' => [
                'item' => $vrItem,
                'total_cost' => $vrTotal,
            ],
            'VS' => [
                'item' => $vsItem,
                'total_cost' => $vsTotal,
            ],
            'group_total' => $veTotal + $vrTotal + $vsTotal,
        ];
    }

    /**
     * Calcular costes con fórmula genérica
     * Usado como fallback o para categorías sin fórmula específica
     */
    public function calculateGeneric(Report $report): array
    {
        $results = [
            'items' => [],
            'totals' => [
                'VR' => 0,
                'VE' => 0,
                'VS' => 0,
                'total' => 0,
            ],
            'errors' => [],
        ];

        // Eliminar costes anteriores
        $report->costItems()->delete();

        // Obtener grupos de detalles
        $groups = $report->details()
            ->select('group_key')
            ->distinct()
            ->pluck('group_key');

        foreach ($groups as $groupKey) {
            $details = $report->details()
                ->where('group_key', $groupKey)
                ->get()
                ->keyBy('field_key');

            $conceptName = $details->first()?->value ?? ucfirst(str_replace('_', ' ', $groupKey));
            $cantidad = (int) ($details->get('cantidad')?->value ?? 1);
            $costeReposicion = (float) ($details->get('coste_reposicion')?->value ?? 0);
            $veManual = (float) ($details->get('ve')?->value ?? 0);

            // VR simple: coste de reposición * cantidad
            $vrTotal = $costeReposicion > 0 ? $costeReposicion * $cantidad : 500 * $cantidad;

            // VE: valor manual
            $veTotal = $veManual;

            // VS: 20% del VR como valor ecosistémico básico
            $vsTotal = $vrTotal * 0.2;

            ReportCostItem::create([
                'report_id' => $report->id,
                'group_key' => $groupKey,
                'cost_type' => 'VR',
                'concept_name' => $conceptName,
                'base_value' => $vrTotal / $cantidad,
                'cr_value' => $costeReposicion,
                'total_cost' => $vrTotal,
                'coef_info_json' => ['tipo' => 'genérico'],
            ]);

            ReportCostItem::create([
                'report_id' => $report->id,
                'group_key' => $groupKey,
                'cost_type' => 'VE',
                'concept_name' => $conceptName,
                'base_value' => $veTotal,
                'total_cost' => $veTotal,
                'coef_info_json' => ['tipo' => 'genérico'],
            ]);

            ReportCostItem::create([
                'report_id' => $report->id,
                'group_key' => $groupKey,
                'cost_type' => 'VS',
                'concept_name' => $conceptName,
                'base_value' => $vrTotal,
                'gi_value' => 0.2,
                'total_cost' => $vsTotal,
                'coef_info_json' => ['tipo' => 'genérico'],
            ]);

            $results['totals']['VR'] += $vrTotal;
            $results['totals']['VE'] += $veTotal;
            $results['totals']['VS'] += $vsTotal;
        }

        $results['totals']['total'] = $results['totals']['VR'] + $results['totals']['VE'] + $results['totals']['VS'];

        // Actualizar totales en el report
        $report->update([
            'vr_total' => $results['totals']['VR'],
            've_total' => $results['totals']['VE'],
            'vs_total' => $results['totals']['VS'],
            'total_cost' => $results['totals']['total'],
        ]);

        return $results;
    }

    /**
     * Obtener información de las fórmulas para mostrar al usuario
     */
    public static function getFormulaInfo(string $category, ?string $subcategory = null): array
    {
        // Fórmulas específicas por subcategoría
        if ($category === 'Infraestructuras' && $subcategory === 'Extracciones de aguas') {
            return [
                'VE' => [
                    'formula' => 'VE = Volumen × Precio Unitario',
                    'descripcion' => 'Valor del recurso extraído',
                    'variables' => [
                        'Volumen' => 'Cantidad de agua extraída en m³',
                        'Precio Unitario' => 'Coste por m³ de agua',
                    ],
                ],
                'VR' => [
                    'formula' => 'VR = Valor introducido manualmente',
                    'descripcion' => 'Valor de Reposición',
                ],
                'VS' => [
                    'formula' => 'VS = VS_base × T',
                    'descripcion' => 'Valor ecosistémico',
                    'variables' => [
                        'VS_base' => 'Valor ecosistémico base introducido',
                        'T' => 'Coeficiente según origen del agua (Manantial=2.0, Superficial=1.8, Subterránea=1.6, Pozo=1.5, Otros=1.4, Red pública=1.2)',
                    ],
                ],
            ];
        }

        // Fórmulas para Vertido de aguas
        if ($category === 'Vertidos' && $subcategory === 'Vertido de aguas') {
            return [
                'VE' => [
                    'formula' => 'VE = Volumen × Coste Limpieza',
                    'descripcion' => 'Valor del daño por vertido',
                    'variables' => [
                        'Volumen' => 'Cantidad de agua vertida en m³',
                        'Coste Limpieza' => 'Coste de limpieza/tratamiento por m³',
                    ],
                ],
                'VR' => [
                    'formula' => 'VR = Valor introducido manualmente',
                    'descripcion' => 'Valor de Reposición',
                ],
                'VS' => [
                    'formula' => 'VS = Valor introducido manualmente',
                    'descripcion' => 'Valor ecosistémico',
                ],
            ];
        }

        return match ($category) {
            'Biodiversidad' => [
                'VR' => [
                    'formula' => 'VR = [(CB × L × N × B × S) × q] + CR',
                    'descripcion' => 'Valor de Reposición',
                    'variables' => [
                        'CB' => 'Coste Base (300€)',
                        'L' => 'Situación Legal según IUCN (CR=70, EN=60, VU=40, NT=20, LC=6.5, DD=5)',
                        'N' => 'CITES (I=3, II=2, III=1.5, Sin CITES=1)',
                        'B' => 'Madurez (Maduro=1.5, Inmaduro=1.1)',
                        'S' => 'Subcategoría (Comercio=1, Caza furtiva/Cinegéticas/Endemismos=2, EEI=1.5)',
                        'q' => 'Cantidad de individuos',
                        'CR' => 'Coste de Reposición introducido',
                    ],
                ],
                'VE' => [
                    'formula' => 'VE = valor introducido manualmente',
                    'descripcion' => 'Valor del recurso extraido',
                ],
                'VS' => [
                    'formula' => 'VS = VR × IG',
                    'descripcion' => 'Valor ecosistémico',
                    'variables' => [
                        'VR' => 'Valor de Reposición calculado',
                        'IG' => 'Índice de Gravedad (suma ponderada de 4 dimensiones)',
                    ],
                ],
                'IG' => [
                    'formula' => 'IG = Σ(dimensión × 0.25) / 100',
                    'descripcion' => 'Índice de Gravedad',
                    'dimensiones' => [
                        'Ubicación' => 'No protegido=50, Cercano=75, Protegido=100',
                        'Nivel Trófico' => 'Primario=50, Secundario=75, Terciario=100',
                        'Reproducción' => 'Comercial=50, Cautiverio=75, No reproducida=100',
                        'Estado Vital' => 'Sano=50, Herido=75, Muerto=100',
                    ],
                ],
            ],
            'Infraestructuras' => [
                'VE' => [
                    'formula' => 'VE = Volumen × Precio Unitario',
                    'descripcion' => 'Valor del recurso extraído',
                ],
                'VR' => [
                    'formula' => 'VR = Valor manual',
                    'descripcion' => 'Valor de Reposición',
                ],
                'VS' => [
                    'formula' => 'VS = VS_base × T',
                    'descripcion' => 'Valor ecosistémico',
                ],
            ],
            'Vertidos' => [
                'VE' => [
                    'formula' => 'VE = Volumen × Coste Limpieza',
                    'descripcion' => 'Valor del daño por vertido',
                ],
                'VR' => [
                    'formula' => 'VR = Valor manual',
                    'descripcion' => 'Valor de Reposición',
                ],
                'VS' => [
                    'formula' => 'VS = Valor manual',
                    'descripcion' => 'Valor ecosistémico',
                ],
            ],
            default => [
                'VR' => ['formula' => 'VR = CR × cantidad', 'descripcion' => 'Fórmula genérica'],
                'VE' => ['formula' => 'VE = valor manual', 'descripcion' => 'Fórmula genérica'],
                'VS' => ['formula' => 'VS = VR × 0.2', 'descripcion' => 'Fórmula genérica'],
            ],
        };
    }
}
