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
     * RELACIONES
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
     * MÉTODOS DE DETALLES
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
     * MÉTODOS DE COSTES
     */

    /**
     * Verificar si el report tiene costes calculados
     */
    public function hasCostsCalculated(): bool
    {
        return $this->costItems()->exists();
    }

    /**
     * Alias de hasCostsCalculated para mayor claridad
     */
    public function hasCosts(): bool
    {
        return $this->hasCostsCalculated();
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
     * MÉTODOS DE ESTADO
     */

    public function isCompletado(): bool
    {
        return $this->status === self::STATUS_COMPLETADO;
    }

    /**
     * Verificar si el report está finalizado (completado)
     * Un caso finalizado es aquel con estado 'completado'
     */
    public function isFinalizado(): bool
    {
        return $this->status === self::STATUS_COMPLETADO;
    }

    /**
     * Verificar si el report puede ser finalizado
     * Requiere que tenga detalles y costes calculados y que no esté ya completado
     */
    public function canBeFinalized(): bool
    {
        return $this->hasDetails() && $this->hasCostsCalculated() && !$this->isCompletado();
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

}