<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'petitioner_id',
        'petitioner_other',
        'urgency',
        'date_petition',
        'date_damage',
        'status',
        'assigned',
        'assigned_to',
        'pdf_report',
    ];

    protected $casts = [
        'date_petition' => 'date',
        'date_damage' => 'date',
        'assigned' => 'boolean',
    ];

    /**
     * Relaciones
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

    /**
     * Métodos de ayuda para estados
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
}