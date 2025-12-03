<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'group_key',
        'field_key',
        'value',
        'species_id',
        'protected_area_id',
        'order_index',
    ];

    protected $casts = [
        'order_index' => 'integer',
    ];

    /**
     * Relación N:1 con Report
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Relación N:1 con Species (opcional)
     */
    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    /**
     * Relación N:1 con ProtectedArea (opcional)
     */
    public function protectedArea(): BelongsTo
    {
        return $this->belongsTo(ProtectedArea::class);
    }

    public function getSubcategoryAttribute()
    {
        return $this->report->subcategory;
    }

    /**
     * Obtener el Field asociado a este detalle
     */
    public function getFieldAttribute(): ?Field
    {
        return Field::where('key_name', $this->field_key)->first();
    }

    /**
     * Scope: Filtrar por report
     */
    public function scopeForReport($query, int $reportId)
    {
        return $query->where('report_id', $reportId);
    }

    /**
     * Scope: Filtrar por grupo
     */
    public function scopeInGroup($query, string $groupKey)
    {
        return $query->where('group_key', $groupKey);
    }

    /**
     * Scope: Obtener todos los grupos únicos de un report
     */
    public static function getGroupsForReport(int $reportId): array
    {
        return self::where('report_id', $reportId)
            ->distinct()
            ->pluck('group_key')
            ->toArray();
    }

    /**
     * Obtener detalles agrupados por group_key para un report
     */
    public static function getGroupedDetails(int $reportId): \Illuminate\Support\Collection
    {
        return self::where('report_id', $reportId)
            ->orderBy('group_key')
            ->orderBy('order_index')
            ->get()
            ->groupBy('group_key');
    }

    /**
     * Obtener el valor formateado según el tipo de campo
     */
    public function getFormattedValueAttribute(): string
    {
        $field = $this->field;
        
        if (!$field) {
            return $this->value ?? '';
        }

        // Formatear según el tipo
        return match($field->type) {
            'decimal', 'number' => $this->value !== null 
                ? number_format((float) $this->value, 2, ',', '.') . ($field->units ? " {$field->units}" : '')
                : '',
            'date' => $this->value ? date('d/m/Y', strtotime($this->value)) : '',
            'boolean' => $this->value ? 'Sí' : 'No',
            default => $this->value ?? '',
        };
    }

    /**
     * Generar siguiente group_key para un tipo
     */
    public static function generateNextGroupKey(int $reportId, string $prefix = 'group'): string
    {
        $lastGroup = self::where('report_id', $reportId)
            ->where('group_key', 'like', "{$prefix}_%")
            ->orderByRaw("CAST(SUBSTRING(group_key, LENGTH(?) + 2) AS UNSIGNED) DESC", [$prefix])
            ->value('group_key');

        if (!$lastGroup) {
            return "{$prefix}_1";
        }

        $lastNumber = (int) str_replace("{$prefix}_", '', $lastGroup);
        return "{$prefix}_" . ($lastNumber + 1);
    }
}