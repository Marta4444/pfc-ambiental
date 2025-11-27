<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Field extends Model
{
    use HasFactory;

    /**
     * Tipos de campos válidos
     */
    const TYPE_TEXT = 'text';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_NUMBER = 'number';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_SELECT = 'select';
    const TYPE_MULTISELECT = 'multiselect';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_RADIO = 'radio';
    const TYPE_DATE = 'date';
    const TYPE_TIME = 'time';
    const TYPE_DATETIME = 'datetime';
    const TYPE_FILE = 'file';
    const TYPE_BOOLEAN = 'boolean';

    const VALID_TYPES = [
        self::TYPE_TEXT,
        self::TYPE_TEXTAREA,
        self::TYPE_NUMBER,
        self::TYPE_DECIMAL,
        self::TYPE_SELECT,
        self::TYPE_MULTISELECT,
        self::TYPE_CHECKBOX,
        self::TYPE_RADIO,
        self::TYPE_DATE,
        self::TYPE_TIME,
        self::TYPE_DATETIME,
        self::TYPE_FILE,
        self::TYPE_BOOLEAN,
    ];

    protected $fillable = [
        'key_name',
        'label',
        'type',
        'units',
        'options_json',
        'help_text',
        'placeholder',
        'is_numeric',
        'active',
    ];

    protected $casts = [
        'options_json' => 'array',
        'is_numeric' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * Relación N:M con Subcategories
     */
    public function subcategories(): BelongsToMany
    {
        return $this->belongsToMany(Subcategory::class, 'subcategory_fields')
            ->withPivot(['is_required', 'order_index', 'default_value'])
            ->withTimestamps()
            ->orderBy('subcategory_fields.order_index');
    }

    /**
     * Obtener opciones parseadas (si es select/multiselect/radio)
     */
    public function getOptionsAttribute(): ?array
    {
        return $this->options_json;
    }

    /**
     * Verificar si el campo requiere opciones
     */
    public function requiresOptions(): bool
    {
        return in_array($this->type, [
            self::TYPE_SELECT,
            self::TYPE_MULTISELECT,
            self::TYPE_RADIO,
        ]);
    }

    /**
     * Verificar si el campo es de tipo archivo
     */
    public function isFileType(): bool
    {
        return $this->type === self::TYPE_FILE;
    }

    /**
     * Verificar si el campo es de tipo fecha
     */
    public function isDateType(): bool
    {
        return in_array($this->type, [
            self::TYPE_DATE,
            self::TYPE_TIME,
            self::TYPE_DATETIME,
        ]);
    }

    /**
     * Obtener regla de validación base según el tipo
     */
    public function getBaseValidationRule(): string
    {
        return match($this->type) {
            self::TYPE_TEXT => 'string|max:255',
            self::TYPE_TEXTAREA => 'string',
            self::TYPE_NUMBER => 'integer',
            self::TYPE_DECIMAL => 'numeric',
            self::TYPE_SELECT => 'string',
            self::TYPE_MULTISELECT => 'array',
            self::TYPE_CHECKBOX => 'array',
            self::TYPE_RADIO => 'string',
            self::TYPE_DATE => 'date',
            self::TYPE_TIME => 'date_format:H:i',
            self::TYPE_DATETIME => 'date',
            self::TYPE_FILE => 'file',
            self::TYPE_BOOLEAN => 'boolean',
            default => 'string',
        };
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
