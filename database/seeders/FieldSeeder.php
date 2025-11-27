<?php

namespace Database\Seeders;

use App\Models\Field;
use Illuminate\Database\Seeder;

class FieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fields = [
            [
                'key_name' => 'especie',
                'label' => 'Especie',
                'type' => 'text',
                'units' => null,
                'options_json' => null,
                'help_text' => 'Nombre científico o común de la especie afectada',
                'placeholder' => 'Ej: Lynx pardinus',
                'is_numeric' => false,
                'active' => true,
            ],
            [
                'key_name' => 'cantidad',
                'label' => 'Cantidad',
                'type' => 'number',
                'units' => 'unidades',
                'options_json' => null,
                'help_text' => 'Número de individuos o elementos afectados',
                'placeholder' => 'Ej: 5',
                'is_numeric' => true,
                'active' => true,
            ],
            [
                'key_name' => 'madurez',
                'label' => 'Madurez',
                'type' => 'select',
                'units' => null,
                'options_json' => ['Juvenil', 'Subadulto', 'Adulto', 'Senil', 'Desconocido'],
                'help_text' => 'Estado de madurez del individuo',
                'placeholder' => 'Seleccione madurez',
                'is_numeric' => false,
                'active' => true,
            ],
            [
                'key_name' => 'estado_vital',
                'label' => 'Estado Vital',
                'type' => 'select',
                'units' => null,
                'options_json' => ['Vivo', 'Muerto', 'Herido', 'Crítico', 'Desconocido'],
                'help_text' => 'Estado de salud o vitalidad del individuo',
                'placeholder' => 'Seleccione estado',
                'is_numeric' => false,
                'active' => true,
            ],
            [
                'key_name' => 'coste_reposicion',
                'label' => 'Coste de Reposición',
                'type' => 'decimal',
                'units' => '€',
                'options_json' => null,
                'help_text' => 'Coste estimado para reponer o restaurar el daño',
                'placeholder' => 'Ej: 1500.50',
                'is_numeric' => true,
                'active' => true,
            ],
            [
                'key_name' => 've',
                'label' => 'Valor Ecológico (VE)',
                'type' => 'decimal',
                'units' => '€',
                'options_json' => null,
                'help_text' => 'Valor ecológico del daño calculado',
                'placeholder' => 'Ej: 2500.00',
                'is_numeric' => true,
                'active' => true,
            ],
            [
                'key_name' => 'coordenadas_afectacion',
                'label' => 'Coordenadas de Afectación',
                'type' => 'text',
                'units' => null,
                'options_json' => null,
                'help_text' => 'Coordenadas GPS del punto de afectación (lat, long)',
                'placeholder' => 'Ej: 40.4168, -3.7038',
                'is_numeric' => false,
                'active' => true,
            ],
            [
                'key_name' => 'volumen',
                'label' => 'Volumen',
                'type' => 'decimal',
                'units' => 'm³',
                'options_json' => null,
                'help_text' => 'Volumen total del vertido o residuo',
                'placeholder' => 'Ej: 50.5',
                'is_numeric' => true,
                'active' => true,
            ],
            [
                'key_name' => 'tipo_gas',
                'label' => 'Tipo de Gas',
                'type' => 'select',
                'units' => null,
                'options_json' => ['CO2', 'CO', 'NOx', 'SO2', 'CH4', 'Partículas', 'Otros'],
                'help_text' => 'Tipo de gas emitido a la atmósfera',
                'placeholder' => 'Seleccione tipo de gas',
                'is_numeric' => false,
                'active' => true,
            ],
            [
                'key_name' => 'superficie_afectada',
                'label' => 'Superficie Afectada',
                'type' => 'decimal',
                'units' => 'ha',
                'options_json' => null,
                'help_text' => 'Superficie total afectada por el daño',
                'placeholder' => 'Ej: 2.5',
                'is_numeric' => true,
                'active' => true,
            ],
            [
                'key_name' => 'tipo_residuo',
                'label' => 'Tipo de Residuo',
                'type' => 'select',
                'units' => null,
                'options_json' => ['Sólido urbano', 'Peligroso', 'Industrial', 'Construcción', 'Agrícola', 'Sanitario', 'Otros'],
                'help_text' => 'Clasificación del tipo de residuo vertido',
                'placeholder' => 'Seleccione tipo',
                'is_numeric' => false,
                'active' => true,
            ],
            [
                'key_name' => 'caudal',
                'label' => 'Caudal',
                'type' => 'decimal',
                'units' => 'l/s',
                'options_json' => null,
                'help_text' => 'Caudal de agua extraído o vertido',
                'placeholder' => 'Ej: 15.0',
                'is_numeric' => true,
                'active' => true,
            ],
            [
                'key_name' => 'origen_agua',
                'label' => 'Origen del Agua',
                'type' => 'select',
                'units' => null,
                'options_json' => ['Superficial', 'Subterránea', 'Pozo', 'Manantial', 'Red pública', 'Otros'],
                'help_text' => 'Procedencia del agua extraída',
                'placeholder' => 'Seleccione origen',
                'is_numeric' => false,
                'active' => true,
            ],
            [
                'key_name' => 'num_aerogeneradores',
                'label' => 'Número de Aerogeneradores',
                'type' => 'number',
                'units' => 'unidades',
                'options_json' => null,
                'help_text' => 'Cantidad de aerogeneradores involucrados',
                'placeholder' => 'Ej: 12',
                'is_numeric' => true,
                'active' => true,
            ],
            [
                'key_name' => 'metodo_captura',
                'label' => 'Método de Captura',
                'type' => 'select',
                'units' => null,
                'options_json' => ['Arma de fuego', 'Trampa', 'Lazo', 'Veneno', 'Redes', 'Cepo', 'Otros'],
                'help_text' => 'Método utilizado para la caza o captura ilegal',
                'placeholder' => 'Seleccione método',
                'is_numeric' => false,
                'active' => true,
            ],
        ];

        foreach ($fields as $field) {
            Field::updateOrCreate(
                ['key_name' => $field['key_name']],
                $field
            );
        }
    }
}