<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;  //se añade
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function category() {

        return $this->belongsTo(Category::class);
    }

    public function reports() {
        
        return $this->hasMany(Report::class);
    }

    /**
     * Relación N:M con Fields
     */
    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(Field::class, 'subcategory_fields')
            ->withPivot(['is_required', 'order_index', 'default_value'])
            ->withTimestamps()
            ->orderBy('subcategory_fields.order_index');
    }

    /**
     * Obtener solo los campos obligatorios
     */
    public function requiredFields(): BelongsToMany
    {
        return $this->fields()->wherePivot('is_required', true);
    }

    /**
     * Obtener campos ordenados
     */
    public function orderedFields(): BelongsToMany
    {
        return $this->fields()->orderBy('subcategory_fields.order_index', 'asc');
    }

    /**
     * Verificar si la subcategoría tiene campos configurados
     */
    public function hasFields(): bool
    {
        return $this->fields()->exists();
    }

    /**
     * Obtener count de campos
     */
    public function getFieldsCountAttribute(): int
    {
        return $this->fields()->count();
    }

}
