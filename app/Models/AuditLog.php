<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * Acciones CRUD
     */
    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';

    /**
     * Acciones personalizadas
     */
    public const ACTION_LOGIN = 'login';
    public const ACTION_LOGOUT = 'logout';
    public const ACTION_ASSIGN = 'assign';
    public const ACTION_UNASSIGN = 'unassign';
    public const ACTION_SELF_ASSIGN = 'self_assign';
    public const ACTION_CALCULATE_COSTS = 'calculate_costs';
    public const ACTION_RESET_COSTS = 'reset_costs';
    public const ACTION_EXPORT = 'export';
    public const ACTION_VIEW = 'view';

    /**
     * Etiquetas legibles para acciones
     */
    public const ACTION_LABELS = [
        self::ACTION_CREATE => 'Crear',
        self::ACTION_UPDATE => 'Actualizar',
        self::ACTION_DELETE => 'Eliminar',
        self::ACTION_LOGIN => 'Iniciar sesión',
        self::ACTION_LOGOUT => 'Cerrar sesión',
        self::ACTION_ASSIGN => 'Asignar',
        self::ACTION_UNASSIGN => 'Desasignar',
        self::ACTION_SELF_ASSIGN => 'Auto-asignar',
        self::ACTION_CALCULATE_COSTS => 'Calcular costes',
        self::ACTION_RESET_COSTS => 'Resetear costes',
        self::ACTION_EXPORT => 'Exportar',
        self::ACTION_VIEW => 'Visualizar',
    ];

    /**
     * Colores para badges de acciones
     */
    public const ACTION_COLORS = [
        self::ACTION_CREATE => 'green',
        self::ACTION_UPDATE => 'yellow',
        self::ACTION_DELETE => 'red',
        self::ACTION_LOGIN => 'blue',
        self::ACTION_LOGOUT => 'gray',
        self::ACTION_ASSIGN => 'purple',
        self::ACTION_UNASSIGN => 'orange',
        self::ACTION_SELF_ASSIGN => 'indigo',
        self::ACTION_CALCULATE_COSTS => 'teal',
        self::ACTION_RESET_COSTS => 'pink',
        self::ACTION_EXPORT => 'cyan',
        self::ACTION_VIEW => 'slate',
    ];

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener el modelo relacionado dinámicamente
     */
    public function auditable(): ?Model
    {
        if (!$this->model_type || !$this->model_id) {
            return null;
        }

        $modelClass = $this->model_type;
        
        if (!class_exists($modelClass)) {
            return null;
        }

        return $modelClass::find($this->model_id);
    }

    /**
     * Obtener etiqueta legible de la acción
     */
    public function getActionLabelAttribute(): string
    {
        return self::ACTION_LABELS[$this->action] ?? ucfirst($this->action);
    }

    /**
     * Obtener color de la acción
     */
    public function getActionColorAttribute(): string
    {
        return self::ACTION_COLORS[$this->action] ?? 'gray';
    }

    /**
     * Obtener nombre corto del modelo
     */
    public function getModelNameAttribute(): string
    {
        if (!$this->model_type) {
            return '-';
        }

        return class_basename($this->model_type);
    }

    /**
     * Scope para filtrar por usuario
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para filtrar por acción
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope para filtrar por modelo
     */
    public function scopeByModel($query, string $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Scope para las acciones más recientes
     */
    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}
