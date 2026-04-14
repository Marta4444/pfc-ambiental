<?php

namespace App\Helpers;

/**
 * Helper para validación geográfica de España
 * Lee datos de CC.AA., provincias, municipios y bounding boxes desde JSON centralizado
 */
class SpainGeoHelper
{
    /**
     * Cache de datos geográficos
     */
    private static ?array $geoData = null;

    /**
     * Carga los datos geográficos desde el archivo JSON
     */
    private static function loadGeoData(): array
    {
        if (self::$geoData === null) {
            $jsonPath = public_path('data/spain-geo.json');
            
            if (!file_exists($jsonPath)) {
                throw new \RuntimeException('No se encontró el archivo de datos geográficos: ' . $jsonPath);
            }
            
            $json = file_get_contents($jsonPath);
            $data = json_decode($json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Error al parsear JSON de datos geográficos: ' . json_last_error_msg());
            }
            
            self::$geoData = $data['comunidades'] ?? [];
        }
        
        return self::$geoData;
    }

    /**
     * Verifica si una comunidad autónoma es válida
     */
    private static function isValidComunidad(string $comunidad): bool
    {
        return isset(self::loadGeoData()[$comunidad]);
    }

    /**
     * Obtiene las provincias de una comunidad autónoma
     */
    private static function getProvincias(string $comunidad): array
    {
        $data = self::loadGeoData()[$comunidad] ?? null;
        if (!$data || !isset($data['provincias'])) {
            return [];
        }
        return array_keys($data['provincias']);
    }

    /**
     * Verifica si una provincia pertenece a una comunidad autónoma
     */
    private static function isValidProvincia(string $provincia, string $comunidad): bool
    {
        $provincias = self::getProvincias($comunidad);
        return in_array($provincia, $provincias, true);
    }

    /**
     * Obtener el bounding box de una comunidad autónoma
     */
    private static function getComunidadBbox(string $comunidad): ?array
    {
        $data = self::loadGeoData()[$comunidad] ?? null;
        return $data['bbox'] ?? null;
    }
    
    /**
     * Parsea coordenadas del formato "lat,lng"
     */
    public static function parseCoordinates(?string $coordStr): ?array
    {
        if (empty($coordStr)) {
            return null;
        }

        $cleanStr = preg_replace('/\s+/', '', $coordStr);
        $parts = explode(',', $cleanStr);

        if (count($parts) !== 2) {
            return null;
        }

        $lat = filter_var($parts[0], FILTER_VALIDATE_FLOAT);
        $lng = filter_var($parts[1], FILTER_VALIDATE_FLOAT);

        if ($lat === false || $lng === false) {
            return null;
        }

        // Validación básica de rango para España (incluyendo Canarias)
        if ($lat < 27 || $lat > 44 || $lng < -19 || $lng > 5) {
            return null;
        }

        return ['lat' => $lat, 'lng' => $lng];
    }

    /**
     * Verifica si unas coordenadas están dentro de una comunidad autónoma
     */
    private static function validateCoordinatesInComunidad(float $lat, float $lng, string $comunidad): array
    {
        $bbox = self::getComunidadBbox($comunidad);
        
        if (!$bbox) {
            return [
                'valid' => false,
                'message' => 'Comunidad autónoma no encontrada'
            ];
        }

        $inside = $lat >= $bbox['minLat'] && $lat <= $bbox['maxLat'] &&
                  $lng >= $bbox['minLng'] && $lng <= $bbox['maxLng'];

        return [
            'valid' => $inside,
            'message' => $inside 
                ? "Coordenadas válidas para {$comunidad}" 
                : "Las coordenadas no corresponden a {$comunidad}"
        ];
    }

    /**
     * Validación completa de datos geográficos
     * 
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validateGeoData(
        string $comunidad, 
        string $provincia, 
        ?string $coordinates = null
    ): array {
        $errors = [];

        // Validar comunidad
        if (!self::isValidComunidad($comunidad)) {
            $errors['community'] = 'La comunidad autónoma no es válida.';
        }

        // Validar que la provincia pertenece a la comunidad
        if (empty($errors['community']) && !self::isValidProvincia($provincia, $comunidad)) {
            $errors['province'] = "La provincia '{$provincia}' no pertenece a '{$comunidad}'.";
        }

        // Validar coordenadas si se proporcionan
        if (!empty($coordinates)) {
            $coords = self::parseCoordinates($coordinates);
            
            if (!$coords) {
                $errors['coordinates'] = 'Formato de coordenadas inválido. Use: lat,lng (ej: 40.4168,-3.7038)';
            } elseif (empty($errors['community'])) {
                $validation = self::validateCoordinatesInComunidad($coords['lat'], $coords['lng'], $comunidad);
                if (!$validation['valid']) {
                    $errors['coordinates'] = $validation['message'];
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
