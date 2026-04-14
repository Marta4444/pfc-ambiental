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
        // Campos de sincronización API
        'gbif_key',
        'iucn_taxon_id',
        'cites_id',
        'sync_source',
        'sync_status',
        'sync_error',
        'last_sync_attempt',
        'last_synced_at',
        // Taxonomía completa
        'kingdom',
        'phylum',
        'class',
        'order',
        'family',
        'genus',
        // Valor económico
        'base_value',
    ];

    protected $casts = [
        'ccaa_status' => 'array',
        'source_json' => 'array',
        'synced_at' => 'datetime',
        'is_protected' => 'boolean',
        'manually_added' => 'boolean',
        'iucn_assessment_year' => 'integer',
        'last_sync_attempt' => 'datetime',
        'last_synced_at' => 'datetime',
        'base_value' => 'decimal:2',
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
     * Solo estos valores activan is_protected = true
     */
    const BOE_STATUSES = [
        'En peligro de extinción' => 'En peligro de extinción',
        'Vulnerable' => 'Vulnerable',
        'Régimen de protección especial' => 'Régimen de protección especial',
    ];

    /**
     * Categorías de protección por CCAA
     * Valores válidos que pueden aplicar las Comunidades Autónomas
     */
    const CCAA_STATUSES = [
        'En peligro de extinción' => 'En peligro de extinción',
        'Vulnerable' => 'Vulnerable',
        'Sensible a la alteración de su hábitat' => 'Sensible a la alteración de su hábitat',
        'De interés especial' => 'De interés especial',
        'Extinta' => 'Extinta',
    ];

    /**
     * Categorías IUCN que implican protección (amenazadas)
     */
    const IUCN_PROTECTED_CATEGORIES = ['CR', 'EN', 'VU', 'NT'];

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
     * Verificar si un valor BOE es válido
     */
    public static function isValidBoeStatus(?string $status): bool
    {
        return !empty($status) && array_key_exists($status, self::BOE_STATUSES);
    }

    /**
     * Verificar si un valor CCAA es válido
     */
    public static function isValidCcaaStatus(?string $status): bool
    {
        return !empty($status) && array_key_exists($status, self::CCAA_STATUSES);
    }

    /**
     * Verificar si un valor IUCN implica protección
     */
    public static function isProtectedIucnCategory(?string $category): bool
    {
        return !empty($category) && in_array($category, self::IUCN_PROTECTED_CATEGORIES);
    }

    /**
     * Verificar si un valor CITES es válido
     */
    public static function isValidCitesAppendix(?string $appendix): bool
    {
        return !empty($appendix) && array_key_exists($appendix, self::CITES_APPENDICES);
    }

    /**
     * Verificar si la especie tiene alguna protección válida
     * Solo considera valores que estén en las constantes definidas
     */
    public function updateProtectionStatus(): void
    {
        $this->is_protected = self::isValidBoeStatus($this->boe_status) 
            || self::isValidCcaaStatus($this->ccaa_status) 
            || self::isProtectedIucnCategory($this->iucn_category)
            || self::isValidCitesAppendix($this->cites_appendix);
        
        $this->save();
    }


    /**
     * Obtener protección en una CCAA específica
     */
    public function getProtectionInCcaa(string $ccaa): ?string
    {
        return $this->ccaa_status[$ccaa] ?? null;
    }

    /**
     * Scope para buscar especies por nombre científico o común
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('scientific_name', 'LIKE', "%{$term}%")
              ->orWhere('common_name', 'LIKE', "%{$term}%");
        });
    }
}
