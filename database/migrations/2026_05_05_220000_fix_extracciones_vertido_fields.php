<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Insertar campos faltantes en la tabla fields (si no existen ya)
        $missingFields = [
            [
                'key_name'     => 'precio_unitario',
                'label'        => 'Precio Unitario',
                'type'         => 'decimal',
                'units'        => '€/m³',
                'help_text'    => 'Precio por metro cúbico de agua extraída',
                'placeholder'  => 'Ej: 0.85',
                'is_numeric'   => true,
                'active'       => true,
                'options_json' => null,
            ],
            [
                'key_name'     => 'vr_manual',
                'label'        => 'Valor de Reposición (VR)',
                'type'         => 'decimal',
                'units'        => '€',
                'help_text'    => 'Valor de reposición introducido manualmente',
                'placeholder'  => 'Ej: 5000.00',
                'is_numeric'   => true,
                'active'       => true,
                'options_json' => null,
            ],
            [
                'key_name'     => 'vs_manual',
                'label'        => 'Valor Ecosistémico Base (VS)',
                'type'         => 'decimal',
                'units'        => '€',
                'help_text'    => 'Valor ecosistémico base introducido manualmente',
                'placeholder'  => 'Ej: 3000.00',
                'is_numeric'   => true,
                'active'       => true,
                'options_json' => null,
            ],
            [
                'key_name'     => 'coste_limpieza_agua',
                'label'        => 'Coste de Limpieza del Agua',
                'type'         => 'decimal',
                'units'        => '€/m³',
                'help_text'    => 'Coste por metro cúbico de limpieza/tratamiento del agua contaminada',
                'placeholder'  => 'Ej: 2.50',
                'is_numeric'   => true,
                'active'       => true,
                'options_json' => null,
            ],
        ];

        foreach ($missingFields as $field) {
            $exists = DB::table('fields')->where('key_name', $field['key_name'])->exists();
            if (!$exists) {
                DB::table('fields')->insert(array_merge($field, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // 2. Obtener IDs de los campos necesarios
        $fieldIds = DB::table('fields')
            ->whereIn('key_name', ['volumen', 'origen_agua', 'caudal', 'precio_unitario', 'vr_manual', 'vs_manual', 'coste_limpieza_agua'])
            ->pluck('id', 'key_name');

        // 3. Obtener IDs de las subcategorías
        $extraccionId = DB::table('subcategories')->where('name', 'Extracciones de aguas')->value('id');
        $vertidoId    = DB::table('subcategories')->where('name', 'Vertido de aguas')->value('id');

        // 4. Fijar campos de "Extracciones de aguas"
        if ($extraccionId) {
            // Eliminar todos los campos actuales de esta subcategoría
            DB::table('subcategory_fields')->where('subcategory_id', $extraccionId)->delete();

            // Insertar los correctos
            $correctFields = [
                ['key_name' => 'volumen',         'is_required' => true,  'order_index' => 1, 'default_value' => null],
                ['key_name' => 'origen_agua',     'is_required' => true,  'order_index' => 2, 'default_value' => null],
                ['key_name' => 'caudal',          'is_required' => false, 'order_index' => 3, 'default_value' => null],
                ['key_name' => 'precio_unitario', 'is_required' => true,  'order_index' => 4, 'default_value' => null],
                ['key_name' => 'vr_manual',       'is_required' => true,  'order_index' => 5, 'default_value' => '0'],
                ['key_name' => 'vs_manual',       'is_required' => true,  'order_index' => 6, 'default_value' => '0'],
            ];

            foreach ($correctFields as $f) {
                if (isset($fieldIds[$f['key_name']])) {
                    DB::table('subcategory_fields')->insert([
                        'subcategory_id' => $extraccionId,
                        'field_id'       => $fieldIds[$f['key_name']],
                        'is_required'    => $f['is_required'],
                        'order_index'    => $f['order_index'],
                        'default_value'  => $f['default_value'],
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
            }
        }

        // 5. Fijar campos de "Vertido de aguas"
        if ($vertidoId) {
            // Eliminar todos los campos actuales de esta subcategoría
            DB::table('subcategory_fields')->where('subcategory_id', $vertidoId)->delete();

            // Insertar los correctos
            $correctFields = [
                ['key_name' => 'volumen',             'is_required' => true, 'order_index' => 1, 'default_value' => null],
                ['key_name' => 'coste_limpieza_agua', 'is_required' => true, 'order_index' => 2, 'default_value' => null],
                ['key_name' => 'vr_manual',           'is_required' => true, 'order_index' => 3, 'default_value' => '0'],
                ['key_name' => 'vs_manual',           'is_required' => true, 'order_index' => 4, 'default_value' => '0'],
            ];

            foreach ($correctFields as $f) {
                if (isset($fieldIds[$f['key_name']])) {
                    DB::table('subcategory_fields')->insert([
                        'subcategory_id' => $vertidoId,
                        'field_id'       => $fieldIds[$f['key_name']],
                        'is_required'    => $f['is_required'],
                        'order_index'    => $f['order_index'],
                        'default_value'  => $f['default_value'],
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // No revertimos — los datos anteriores eran incorrectos
    }
};
