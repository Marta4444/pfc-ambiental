<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtectedArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'wdpa_id',
        'protection_type',
        'iucn_category',
        'designation',
        'lat_min',
        'lat_max',
        'long_min',
        'long_max',
        'geometry',
        'description',
        'area_km2',
        'region',
        'established_year',
        'source',
        'synced_at',
        'source_json',
        'active',
    ];

    protected $casts = [
        'lat_min' => 'decimal:7',
        'lat_max' => 'decimal:7',
        'long_min' => 'decimal:7',
        'long_max' => 'decimal:7',
        'geometry' => 'array',
        'source_json' => 'array',
        'area_km2' => 'decimal:4',
        'synced_at' => 'datetime',
        'active' => 'boolean',
        'established_year' => 'integer',
    ];

    /**
     * Tipos de protección válidos
     */
    const PROTECTION_TYPES = [
        'Parque Nacional',
        'Parque Natural',
        'Parque Regional',
        'Reserva Natural',
        'Reserva de la Biosfera',
        'Monumento Natural',
        'Paisaje Protegido',
        'ZEPA', // Zona de Especial Protección para las Aves
        'LIC',  // Lugar de Importancia Comunitaria
        'ZEC',  // Zona Especial de Conservación
        'Humedal Ramsar',
        'Espacio Natural Protegido',
        'Área Marina Protegida',
        'Microrreserva',
        'Otro',
    ];

    /**
     * Categorías IUCN
     */
    const IUCN_CATEGORIES = [
        'Ia' => 'Reserva Natural Estricta',
        'Ib' => 'Área Silvestre',
        'II' => 'Parque Nacional',
        'III' => 'Monumento Natural',
        'IV' => 'Área de Manejo de Hábitat/Especies',
        'V' => 'Paisaje Terrestre/Marino Protegido',
        'VI' => 'Área Protegida con Uso Sostenible',
    ];

    /**
     * Relación 1:N con ReportDetails
     */
    public function reportDetails()
    {
        return $this->hasMany(ReportDetail::class, 'protected_area_id');
    }

    /**
     * Verificar si un punto está dentro del bounding box
     */
    public function containsPointInBoundingBox(float $lat, float $long): bool
    {
        if (is_null($this->lat_min) || is_null($this->lat_max) || 
            is_null($this->long_min) || is_null($this->long_max)) {
            return false;
        }

        return $lat >= $this->lat_min 
            && $lat <= $this->lat_max 
            && $long >= $this->long_min 
            && $long <= $this->long_max;
    }

    /**
     * Verificar si un punto está dentro de la geometría (más preciso)
     * Usa el algoritmo ray-casting para polígonos
     */
    public function containsPoint(float $lat, float $long): bool
    {
        // Primero verificar bounding box (rápido)
        if (!$this->containsPointInBoundingBox($lat, $long)) {
            return false;
        }

        // Si no hay geometría detallada, usar solo bounding box
        if (empty($this->geometry)) {
            return true;
        }

        // Verificar con geometría precisa
        return $this->pointInPolygon($lat, $long, $this->geometry);
    }

    /**
     * Algoritmo ray-casting para verificar punto en polígono
     */
    protected function pointInPolygon(float $lat, float $long, array $geometry): bool
    {
        // Soportar GeoJSON Polygon y MultiPolygon
        $type = $geometry['type'] ?? '';
        $coordinates = $geometry['coordinates'] ?? [];

        if ($type === 'Polygon') {
            return $this->pointInSinglePolygon($lat, $long, $coordinates[0] ?? []);
        }

        if ($type === 'MultiPolygon') {
            foreach ($coordinates as $polygon) {
                if ($this->pointInSinglePolygon($lat, $long, $polygon[0] ?? [])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Ray-casting para un polígono simple
     */
    protected function pointInSinglePolygon(float $lat, float $long, array $ring): bool
    {
        $inside = false;
        $n = count($ring);

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $ring[$i][0]; // longitude
            $yi = $ring[$i][1]; // latitude
            $xj = $ring[$j][0];
            $yj = $ring[$j][1];

            if ((($yi > $lat) !== ($yj > $lat)) &&
                ($long < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    /**
     * Scope: Buscar áreas que contengan un punto (por bounding box)
     */
    public function scopeContainingPoint($query, float $lat, float $long)
    {
        return $query->where('active', true)
            ->where('lat_min', '<=', $lat)
            ->where('lat_max', '>=', $lat)
            ->where('long_min', '<=', $long)
            ->where('long_max', '>=', $long);
    }

    /**
     * Scope: Filtrar por tipo de protección
     */
    public function scopeByProtectionType($query, string $type)
    {
        return $query->where('protection_type', $type);
    }

    /**
     * Scope: Filtrar por región (CCAA)
     */
    public function scopeByRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Scope: Solo activas
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Buscar todas las áreas protegidas que contengan un punto
     * Devuelve colección con verificación precisa de geometría
     */
    public static function findAreasContainingPoint(float $lat, float $long): \Illuminate\Support\Collection
    {
        // Buscar por bounding box primero (rápido)
        $candidates = self::containingPoint($lat, $long)->get();

        // Filtrar por geometría precisa
        return $candidates->filter(function ($area) use ($lat, $long) {
            return $area->containsPoint($lat, $long);
        });
    }

    /**
     * Verificar si un punto está en alguna área protegida
     */
    public static function isPointProtected(float $lat, float $long): bool
    {
        return self::findAreasContainingPoint($lat, $long)->isNotEmpty();
    }

    /**
     * Obtener información de protección para un punto
     */
    public static function getProtectionInfo(float $lat, float $long): array
    {
        $areas = self::findAreasContainingPoint($lat, $long);

        return [
            'is_protected' => $areas->isNotEmpty(),
            'areas_count' => $areas->count(),
            'areas' => $areas->map(function ($area) {
                return [
                    'id' => $area->id,
                    'name' => $area->name,
                    'protection_type' => $area->protection_type,
                    'iucn_category' => $area->iucn_category,
                    'designation' => $area->designation,
                    'region' => $area->region,
                ];
            })->values()->toArray(),
            'highest_protection' => $areas->sortBy(function ($area) {
                // Ordenar por importancia (Parque Nacional > ZEPA > etc.)
                $priority = [
                    'Parque Nacional' => 1,
                    'Reserva Natural' => 2,
                    'Parque Natural' => 3,
                    'ZEPA' => 4,
                    'ZEC' => 5,
                    'LIC' => 6,
                ];
                return $priority[$area->protection_type] ?? 99;
            })->first()?->protection_type,
        ];
    }
}