<?php

namespace Database\Seeders;

use App\Models\Field;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

/**
 * Seeder para configurar los campos específicos de Vertido de Aguas.
 * 
 * Este seeder puede ejecutarse de forma independiente sin borrar la base de datos:
 * php artisan db:seed --class=VertidoAguasFieldSeeder
 *
 */
class VertidoAguasFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear los nuevos campos en la tabla fields
        $newFields = [
            [
                'key_name' => 'coste_limpieza_agua',
                'label' => 'Coste de Limpieza del Agua',
                'type' => 'decimal',
                'units' => '€/m³',
                'options_json' => null,
                'help_text' => 'Coste por metro cúbico de limpieza/tratamiento del agua contaminada',
                'placeholder' => 'Ej: 2.50',
                'is_numeric' => true,
                'active' => true,
            ],
            [
                'key_name' => 'vr_manual',
                'label' => 'Valor de Reposición (VR)',
                'type' => 'decimal',
                'units' => '€',
                'options_json' => null,
                'help_text' => 'Valor de reposición introducido manualmente',
                'placeholder' => 'Ej: 5000.00',
                'is_numeric' => true,
                'active' => true,
            ],
            [
                'key_name' => 'vs_manual',
                'label' => 'Valor Ecosistémico (VS)',
                'type' => 'decimal',
                'units' => '€',
                'options_json' => null,
                'help_text' => 'Valor ecosistémico introducido manualmente',
                'placeholder' => 'Ej: 3000.00',
                'is_numeric' => true,
                'active' => true,
            ],
        ];

        foreach ($newFields as $field) {
            Field::updateOrCreate(
                ['key_name' => $field['key_name']],
                $field
            );
        }

        // 2. Obtener todos los campos
        $fields = Field::pluck('id', 'key_name');

        // 3. Asociar campos a la subcategoría "Vertido de aguas"
        
        $vertidoAguas = Subcategory::where('name', 'Vertido de aguas')->first();
        
        if ($vertidoAguas) {
            // Primero desvincular los campos que sobran
            $camposAQuitar = ['caudal', 'coordenadas_afectacion'];
            foreach ($camposAQuitar as $keyName) {
                if (isset($fields[$keyName])) {
                    $vertidoAguas->fields()->detach($fields[$keyName]);
                }
            }

            // Ahora vincular solo los campos nuevos
            $vertidoAguasFields = [
                'volumen' => ['is_required' => true, 'order_index' => 1, 'default_value' => null],
                'coste_limpieza_agua' => ['is_required' => true, 'order_index' => 2, 'default_value' => null],
                'vr_manual' => ['is_required' => true, 'order_index' => 3, 'default_value' => '0'],
                'vs_manual' => ['is_required' => true, 'order_index' => 4, 'default_value' => '0'],
            ];

            foreach ($vertidoAguasFields as $keyName => $pivotData) {
                if (isset($fields[$keyName])) {
                    $vertidoAguas->fields()->syncWithoutDetaching([
                        $fields[$keyName] => $pivotData
                    ]);
                }
            }

            $this->command->info('✅ Campos de Vertido de Aguas configurados correctamente.');
            $this->command->info('   - Añadidos: volumen, coste_limpieza_agua, vr_manual, vs_manual');
            $this->command->info('   - Eliminados del formulario: caudal, coordenadas_afectacion');
        } else {
            $this->command->error('❌ No se encontró la subcategoría "Vertido de aguas".');
        }
    }
}
