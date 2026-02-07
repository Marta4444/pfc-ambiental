<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Species extends Model
{
    use HasFactory;

    protected $table = 'species';

    protected $fillable = [
        'scientific_name',
        'common_name',
        'taxon_group',
        'boe_status',
        'boe_law_ref',
        'ccaa_status',
        'iucn_category',
        'iucn_assessment_year',
        'cites_appendix',
        'synced_at',
        'source_json',
        'is_protected',
        'manually_added',
    ];

    protected $casts = [
        'ccaa_status' => 'array',
        'source_json' => 'array',
        'synced_at' => 'datetime',
        'is_protected' => 'boolean',
        'manually_added' => 'boolean',
        'iucn_assessment_year' => 'integer',
    ];

    /**
     * Grupos taxonómicos válidos
     */
    const TAXON_GROUPS = [
        'Mamíferos',
        'Aves',
        'Reptiles',
        'Anfibios',
        'Peces',
        'Invertebrados',
        'Flora',
    ];

    /**
     * Categorías BOE (Catálogo Español de Especies Amenazadas)
     */
    const BOE_STATUSES = [
        'En peligro de extinción',
        'Vulnerable',
        'Régimen de protección especial',
    ];

    /**
     * Categorías IUCN
     */
    const IUCN_CATEGORIES = [
        'EX' => 'Extinto',
        'EW' => 'Extinto en estado silvestre',
        'CR' => 'En peligro crítico',
        'EN' => 'En peligro',
        'VU' => 'Vulnerable',
        'NT' => 'Casi amenazado',
        'LC' => 'Preocupación menor',
        'DD' => 'Datos insuficientes',
        'NE' => 'No evaluado',
    ];

    /**
     * Apéndices CITES
     */
    const CITES_APPENDICES = [
        'I' => 'Apéndice I - Prohibido comercio',
        'II' => 'Apéndice II - Comercio regulado',
        'III' => 'Apéndice III - Protección nacional',
    ];

    /**
     * Relación 1:N con ReportDetails
     * Una especie puede aparecer en múltiples detalles de reportes
     */
    public function reportDetails()
    {
        return $this->hasMany(ReportDetail::class, 'species_id');
    }    

    /**
     * Verificar si la especie tiene alguna protección
     */
    public function updateProtectionStatus(): void
    {
        $this->is_protected = !empty($this->boe_status) 
            || !empty($this->ccaa_status) 
            || in_array($this->iucn_category, ['CR', 'EN', 'VU', 'NT'])
            || !empty($this->cites_appendix);
        
        $this->save();
    }

    /**
     * Obtener el nivel de protección más alto
     */
    public function getHighestProtectionAttribute(): ?string
    {
        if ($this->boe_status === 'En peligro de extinción' || $this->iucn_category === 'CR') {
            return 'Crítico';
        }
        if ($this->boe_status === 'Vulnerable' || in_array($this->iucn_category, ['EN', 'VU'])) {
            return 'Alto';
        }
        if ($this->boe_status === 'Régimen de protección especial' || $this->iucn_category === 'NT') {
            return 'Medio';
        }
        if ($this->cites_appendix) {
            return 'CITES';
        }
        return null;
    }

    /**
     * Obtener protección en una CCAA específica
     */
    public function getProtectionInCcaa(string $ccaa): ?string
    {
        return $this->ccaa_status[$ccaa] ?? null;
    }

    /**
     * Buscar especies por nombre (científico o común)
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('scientific_name', 'LIKE', "%{$term}%")
              ->orWhere('common_name', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Filtrar solo especies protegidas
     */
    public function scopeProtected($query)
    {
        return $query->where('is_protected', true);
    }

    /**
     * Filtrar por grupo taxonómico
     */
    public function scopeByTaxonGroup($query, string $group)
    {
        return $query->where('taxon_group', $group);
    }

    /**
     * Especies que necesitan sincronización (más de X días sin actualizar)
     */
    public function scopeNeedsSync($query, int $days = 30)
    {
        return $query->where(function ($q) use ($days) {
            $q->whereNull('synced_at')
              ->orWhere('synced_at', '<', now()->subDays($days));
        });
    }

    /**
     * Obtener resumen de protección para mostrar al usuario
     */
    public function getProtectionSummaryAttribute(): array
    {
        $summary = [];

        if ($this->boe_status) {
            $summary['nacional'] = [
                'status' => $this->boe_status,
                'law' => $this->boe_law_ref,
            ];
        }

        if ($this->ccaa_status && is_array($this->ccaa_status) && count($this->ccaa_status) > 0) {
            $summary['autonomica'] = $this->ccaa_status;
        } elseif ($this->ccaa_status && is_string($this->ccaa_status)) {
            $summary['autonomica'] = [$this->ccaa_status];
        }

        if ($this->iucn_category) {
            $summary['iucn'] = [
                'category' => $this->iucn_category,
                'label' => self::IUCN_CATEGORIES[$this->iucn_category] ?? $this->iucn_category,
                'year' => $this->iucn_assessment_year,
            ];
        }

        if ($this->cites_appendix) {
            $summary['cites'] = [
                'appendix' => $this->cites_appendix,
                'label' => self::CITES_APPENDICES[$this->cites_appendix] ?? "Apéndice {$this->cites_appendix}",
            ];
        }

        return $summary;
    }
}
