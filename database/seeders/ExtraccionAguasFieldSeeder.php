<?php

namespace Database\Seeders;

use App\Models\Field;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

/**
 * Seeder para añadir los campos específicos de Extracción de Aguas.
 * 
 * Este seeder puede ejecutarse de forma independiente sin borrar la base de datos:
 * php artisan db:seed --class=ExtraccionAguasFieldSeeder
 */
class ExtraccionAguasFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear los nuevos campos en la tabla fields
        $newFields = [
            [
                'key_name' => 'precio_unitario',
                'label' => 'Precio Unitario',
                'type' => 'decimal',
                'units' => '€/m³',
                'options_json' => null,
                'help_text' => 'Precio por metro cúbico de agua extraída',
                'placeholder' => 'Ej: 0.85',
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
                'label' => 'Valor Ecosistémico Base (VS)',
                'type' => 'decimal',
                'units' => '€',
                'options_json' => null,
                'help_text' => 'Valor ecosistémico base. Se multiplicará por el coeficiente T según el origen del agua.',
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

        // 2. Obtener todos los campos (incluidos los nuevos)
        $fields = Field::pluck('id', 'key_name');

        // 3. Asociar campos a la subcategoría "Extracciones de aguas"
        $extraccionAguas = Subcategory::where('name', 'Extracciones de aguas')->first();
        
        if ($extraccionAguas) {
            $extraccionAguasFields = [
                'volumen' => ['is_required' => true, 'order_index' => 1, 'default_value' => null],
                'origen_agua' => ['is_required' => true, 'order_index' => 2, 'default_value' => null],
                'caudal' => ['is_required' => false, 'order_index' => 3, 'default_value' => null],
                'precio_unitario' => ['is_required' => true, 'order_index' => 4, 'default_value' => null],
                'vr_manual' => ['is_required' => true, 'order_index' => 5, 'default_value' => '0'],
                'vs_manual' => ['is_required' => true, 'order_index' => 6, 'default_value' => '0'],
            ];

            foreach ($extraccionAguasFields as $keyName => $pivotData) {
                if (isset($fields[$keyName])) {
                    $extraccionAguas->fields()->syncWithoutDetaching([
                        $fields[$keyName] => $pivotData
                    ]);
                }
            }

            $this->command->info('Campos de Extracción de Aguas actualizados correctamente.');
        } else {
            $this->command->error('No se encontró la subcategoría "Extracciones de aguas".');
        }
    }
}
