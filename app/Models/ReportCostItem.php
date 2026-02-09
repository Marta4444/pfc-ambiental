<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCostItem extends Model
{
    use HasFactory;

    /**
     * Tipos de coste disponibles
     */
    public const COST_TYPE_VR = 'VR'; // Valor de Reposición
    public const COST_TYPE_VE = 'VE'; // Valor del recurso extraido
    public const COST_TYPE_VS = 'VS'; // Valor ecosistémico

    public const COST_TYPES = [
        self::COST_TYPE_VR => 'Valor de Reposición',
        self::COST_TYPE_VE => 'Valor del recurso extraido',
        self::COST_TYPE_VS => 'Valor ecosistémico',
    ];

    protected $fillable = [
        'report_id',
        'group_key',
        'cost_type',
        'concept_name',
        'base_value',
        'cr_value',
        'gi_value',
        'total_cost',
        'coef_info_json',
    ];

    protected $casts = [
        'base_value' => 'decimal:2',
        'cr_value' => 'decimal:4',
        'gi_value' => 'decimal:4',
        'total_cost' => 'decimal:2',
        'coef_info_json' => 'array',
    ];

    /**
     * Relación con Report
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Obtener el nombre legible del tipo de coste
     */
    public function getCostTypeLabelAttribute(): string
    {
        return self::COST_TYPES[$this->cost_type] ?? $this->cost_type;
    }

    /**
     * Scope para filtrar por tipo de coste
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('cost_type', $type);
    }

    /**
     * Scope para filtrar por grupo
     */
    public function scopeOfGroup($query, string $groupKey)
    {
        return $query->where('group_key', $groupKey);
    }

    /**
     * Obtener la suma de costes por tipo para un report
     */
    public static function getTotalsByType(int $reportId): array
    {
        $totals = self::where('report_id', $reportId)
            ->selectRaw('cost_type, SUM(total_cost) as total')
            ->groupBy('cost_type')
            ->pluck('total', 'cost_type')
            ->toArray();

        return [
            'VR' => (float) ($totals['VR'] ?? 0),
            'VE' => (float) ($totals['VE'] ?? 0),
            'VS' => (float) ($totals['VS'] ?? 0),
            'total' => array_sum($totals),
        ];
    }
}
