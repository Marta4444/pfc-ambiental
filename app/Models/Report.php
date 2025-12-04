<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    use HasFactory;

    /**
     * Estados válidos para los reports
     */
    const STATUS_NUEVO = 'nuevo';
    const STATUS_EN_PROCESO = 'en_proceso';
    const STATUS_EN_ESPERA = 'en_espera';
    const STATUS_COMPLETADO = 'completado';

    /**
     * Array de estados válidos
     */
    const VALID_STATUSES = [
        self::STATUS_NUEVO,
        self::STATUS_EN_PROCESO,
        self::STATUS_EN_ESPERA,
        self::STATUS_COMPLETADO,
    ];

    /**
     * Etiquetas legibles para los estados
     */
    const STATUS_LABELS = [
        self::STATUS_NUEVO => 'Nuevo',
        self::STATUS_EN_PROCESO => 'En Proceso',
        self::STATUS_EN_ESPERA => 'En Espera',
        self::STATUS_COMPLETADO => 'Completado',
    ];

    /**
     * Niveles de urgencia válidos
     */
    const URGENCY_NORMAL = 'normal';
    const URGENCY_ALTA = 'alta';
    const URGENCY_URGENTE = 'urgente';

    /**
     * Array de urgencias válidas
     */
    const VALID_URGENCIES = [
        self::URGENCY_NORMAL,
        self::URGENCY_ALTA,
        self::URGENCY_URGENTE,
    ];

    /**
     * Etiquetas legibles para las urgencias
     */
    const URGENCY_LABELS = [
        self::URGENCY_NORMAL => 'Normal',
        self::URGENCY_ALTA => 'Alta',
        self::URGENCY_URGENTE => 'Urgente',
    ];

    protected $fillable = [
        'user_id',
        'category_id',
        'subcategory_id',
        'ip',
        'title',
        'background',
        'community',
        'province',
        'locality',
        'coordinates',
        'petitioner_id',
        'petitioner_other',
        'office',
        'diligency',
        'urgency',
        'date_petition',
        'date_damage',
        'status',
        'assigned',
        'assigned_to',
        'pdf_report',
        'vr_total',
        've_total',
        'vs_total',
        'total_cost',
    ];

    protected $casts = [
        'date_petition' => 'date',
        'date_damage' => 'date',
        'assigned' => 'boolean',
        'vr_total' => 'decimal:2',
        've_total' => 'decimal:2',
        'vs_total' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /**
     * ==========================================
     * RELACIONES
     * ==========================================
     */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function petitioner(): BelongsTo
    {
        return $this->belongsTo(Petitioner::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function details(): HasMany
    {
        return $this->hasMany(ReportDetail::class);
    }

    /**
     * Relación con los items de coste
     */
    public function costItems(): HasMany
    {
        return $this->hasMany(ReportCostItem::class);
    }

    /**
     * ==========================================
     * MÉTODOS DE DETALLES
     * ==========================================
     */

    /**
     * Verificar si el report tiene detalles
     */
    public function hasDetails(): bool
    {
        return $this->details()->exists();
    }

    /**
     * Obtener detalles agrupados
     */
    public function getGroupedDetails(): \Illuminate\Support\Collection
    {
        return ReportDetail::getGroupedDetails($this->id);
    }

    /**
     * Obtener cantidad de grupos de detalles
     */
    public function getDetailsGroupsCountAttribute(): int
    {
        return count(ReportDetail::getGroupsForReport($this->id));
    }

    /**
     * Obtener los grupos únicos de detalles
     */
    public function getDetailGroups(): array
    {
        return $this->details()
            ->select('group_key')
            ->distinct()
            ->pluck('group_key')
            ->toArray();
    }

    /**
     * ==========================================
     * MÉTODOS DE COSTES
     * ==========================================
     */

    /**
     * Verificar si el report tiene costes calculados
     */
    public function hasCostsCalculated(): bool
    {
        return $this->costItems()->exists();
    }

    /**
     * Obtener items de coste agrupados por group_key
     */
    public function getGroupedCostItems(): \Illuminate\Support\Collection
    {
        return $this->costItems()
            ->orderBy('group_key')
            ->orderByRaw("FIELD(cost_type, 'VR', 'VE', 'VS')")
            ->get()
            ->groupBy('group_key');
    }

    /**
     * Obtener totales por tipo de coste
     */
    public function getCostTotals(): array
    {
        if (!$this->hasCostsCalculated()) {
            return [
                'VR' => 0,
                'VE' => 0,
                'VS' => 0,
                'total' => 0,
            ];
        }

        return ReportCostItem::getTotalsByType($this->id);
    }

    /**
     * Actualizar totales desde cost_items
     */
    public function updateTotalsFromCostItems(): void
    {
        $totals = ReportCostItem::getTotalsByType($this->id);

        $this->update([
            'vr_total' => $totals['VR'],
            've_total' => $totals['VE'],
            'vs_total' => $totals['VS'],
            'total_cost' => $totals['total'],
        ]);
    }

    /**
     * Resetear todos los costes del report
     */
    public function resetCosts(): void
    {
        $this->costItems()->delete();

        $this->update([
            'vr_total' => 0,
            've_total' => 0,
            'vs_total' => 0,
            'total_cost' => 0,
        ]);
    }

    /**
     * Obtener el total formateado
     */
    public function getFormattedTotalCostAttribute(): string
    {
        return number_format((float) $this->total_cost, 2, ',', '.') . ' €';
    }

    /**
     * Obtener VR total formateado
     */
    public function getFormattedVrTotalAttribute(): string
    {
        return number_format((float) $this->vr_total, 2, ',', '.') . ' €';
    }

    /**
     * Obtener VE total formateado
     */
    public function getFormattedVeTotalAttribute(): string
    {
        return number_format((float) $this->ve_total, 2, ',', '.') . ' €';
    }

    /**
     * Obtener VS total formateado
     */
    public function getFormattedVsTotalAttribute(): string
    {
        return number_format((float) $this->vs_total, 2, ',', '.') . ' €';
    }

    /**
     * ==========================================
     * MÉTODOS DE ESTADO
     * ==========================================
     */

    public function isNuevo(): bool
    {
        return $this->status === self::STATUS_NUEVO;
    }

    public function isEnProceso(): bool
    {
        return $this->status === self::STATUS_EN_PROCESO;
    }

    public function isEnEspera(): bool
    {
        return $this->status === self::STATUS_EN_ESPERA;
    }

    public function isCompletado(): bool
    {
        return $this->status === self::STATUS_COMPLETADO;
    }

    /**
     * Obtener etiqueta legible del estado
     */
    public function getStatusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Obtener etiqueta legible de la urgencia
     */
    public function getUrgencyLabel(): string
    {
        return self::URGENCY_LABELS[$this->urgency] ?? $this->urgency;
    }

    /**
     * Obtener color de badge según estado
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_NUEVO => 'blue',
            self::STATUS_EN_PROCESO => 'yellow',
            self::STATUS_EN_ESPERA => 'orange',
            self::STATUS_COMPLETADO => 'green',
            default => 'gray',
        };
    }

    /**
     * Obtener color de badge según urgencia
     */
    public function getUrgencyColor(): string
    {
        return match($this->urgency) {
            self::URGENCY_NORMAL => 'gray',
            self::URGENCY_ALTA => 'orange',
            self::URGENCY_URGENTE => 'red',
            default => 'gray',
        };
    }

    /**
     * ==========================================
     * SCOPES
     * ==========================================
     */

    /**
     * Scope para reports con costes calculados
     */
    public function scopeWithCostsCalculated($query)
    {
        return $query->whereHas('costItems');
    }

    /**
     * Scope para reports sin costes calculados
     */
    public function scopeWithoutCostsCalculated($query)
    {
        return $query->whereDoesntHave('costItems');
    }

    /**
     * Scope para reports con detalles
     */
    public function scopeWithDetails($query)
    {
        return $query->whereHas('details');
    }

    /**
     * Scope para reports listos para calcular costes (tienen detalles pero no costes)
     */
    public function scopeReadyForCostCalculation($query)
    {
        return $query->whereHas('details')->whereDoesntHave('costItems');
    }
}