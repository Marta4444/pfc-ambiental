<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Petitioner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'active',
        'order',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Scope para obtener solo peticionarios activos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para ordenar por campo order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}