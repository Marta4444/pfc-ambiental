<?php

namespace Database\Seeders;

use App\Models\Field;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class SubcategoryFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener campos por key_name
        $fields = Field::pluck('id', 'key_name');

        // BIODIVERSIDAD - Caza furtiva (campos específicos)

        $cazaFurtiva = Subcategory::where('name', 'Caza furtiva')->first();
        if ($cazaFurtiva) {
            $cazaFurtivaFields = [
                'especie' => ['is_required' => true, 'order_index' => 1, 'default_value' => null],
                'boe_status' => ['is_required' => true, 'order_index' => 2, 'default_value' => null],
                'ccaa_status' => ['is_required' => true, 'order_index' => 3, 'default_value' => null],
                'iucn_category' => ['is_required' => true, 'order_index' => 4, 'default_value' => null],
                'cantidad' => ['is_required' => true, 'order_index' => 5, 'default_value' => '1'],
                'madurez' => ['is_required' => false, 'order_index' => 6, 'default_value' => 'Desconocido'],
                'estado_vital' => ['is_required' => true, 'order_index' => 7, 'default_value' => null],
                'coste_reposicion' => ['is_required' => false, 'order_index' => 8, 'default_value' => null],
                've' => ['is_required' => false, 'order_index' => 9, 'default_value' => null],
                
            ];

            foreach ($cazaFurtivaFields as $keyName => $pivotData) {
                if (isset($fields[$keyName])) {
                    $cazaFurtiva->fields()->syncWithoutDetaching([
                        $fields[$keyName] => $pivotData
                    ]);
                }
            }
        }

        // BIODIVERSIDAD - Comercio
      
        $comercio = Subcategory::where('name', 'Comercio')->first();
        if ($comercio) {
            $comercioFields = [
                'especie' => ['is_required' => true, 'order_index' => 1, 'default_value' => null],
                'boe_status' => ['is_required' => true, 'order_index' => 2, 'default_value' => null],
                'ccaa_status' => ['is_required' => true, 'order_index' => 3, 'default_value' => null],
                'iucn_category' => ['is_required' => true, 'order_index' => 4, 'default_value' => null],
                'cantidad' => ['is_required' => true, 'order_index' => 5, 'default_value' => '1'],
                'madurez' => ['is_required' => false, 'order_index' => 6, 'default_value' => 'Desconocido'],
                'estado_vital' => ['is_required' => true, 'order_index' => 7, 'default_value' => null],
                'coste_reposicion' => ['is_required' => false, 'order_index' => 8, 'default_value' => null],
                've' => ['is_required' => false, 'order_index' => 9, 'default_value' => null],
            ];

            foreach ($comercioFields as $keyName => $pivotData) {
                if (isset($fields[$keyName])) {
                    $comercio->fields()->syncWithoutDetaching([
                        $fields[$keyName] => $pivotData
                    ]);
                }
            }
        }

        // BIODIVERSIDAD - Especie Exótica Invasora (EEI)
  
        $eei = Subcategory::where('name', 'Especie Exótica Invasora (EEI)')->first();
        if ($eei) {
            $eeiFields = [
                'especie' => ['is_required' => true, 'order_index' => 1, 'default_value' => null],
                'boe_status' => ['is_required' => true, 'order_index' => 2, 'default_value' => null],
                'ccaa_status' => ['is_required' => true, 'order_index' => 3, 'default_value' => null],
                'iucn_category' => ['is_required' => true, 'order_index' => 4, 'default_value' => null],
                'cantidad' => ['is_required' => true, 'order_index' => 5, 'default_value' => '1'],
                'madurez' => ['is_required' => false, 'order_index' => 6, 'default_value' => 'Desconocido'],
                'estado_vital' => ['is_required' => true, 'order_index' => 7, 'default_value' => null],
                'coste_reposicion' => ['is_required' => false, 'order_index' => 8, 'default_value' => null],
                've' => ['is_required' => false, 'order_index' => 9, 'default_value' => null],
            ];

            foreach ($eeiFields as $keyName => $pivotData) {
                if (isset($fields[$keyName])) {
                    $eei->fields()->syncWithoutDetaching([
                        $fields[$keyName] => $pivotData
                    ]);
                }
            }
        }

        // INFRAESTRUCTURAS - Extracciones de aguas

        $extraccionAguas = Subcategory::where('name', 'Extracciones de aguas')->first();
        if ($extraccionAguas) {
            $extraccionAguasFields = [
                'caudal' => ['is_required' => true, 'order_index' => 1, 'default_value' => null],
                'origen_agua' => ['is_required' => true, 'order_index' => 2, 'default_value' => null],
                'volumen' => ['is_required' => false, 'order_index' => 3, 'default_value' => null],
                            ];

            foreach ($extraccionAguasFields as $keyName => $pivotData) {
                if (isset($fields[$keyName])) {
                    $extraccionAguas->fields()->syncWithoutDetaching([
                        $fields[$keyName] => $pivotData
                    ]);
                }
            }
        }

        // INFRAESTRUCTURAS - Parques eólicos

        $parquesEolicos = Subcategory::where('name', 'Parques eólicos')->first();
        if ($parquesEolicos) {
            $parquesEolicosFields = [
                'num_aerogeneradores' => ['is_required' => false, 'order_index' => 1, 'default_value' => null],
                'especie' => ['is_required' => false, 'order_index' => 2, 'default_value' => null],
                'cantidad' => ['is_required' => false, 'order_index' => 3, 'default_value' => null],
                'superficie_afectada' => ['is_required' => false, 'order_index' => 4, 'default_value' => null],
            ];

            foreach ($parquesEolicosFields as $keyName => $pivotData) {
                if (isset($fields[$keyName])) {
                    $parquesEolicos->fields()->syncWithoutDetaching([
                        $fields[$keyName] => $pivotData
                    ]);
                }
            }
        }

        // VERTIDOS - Vertido de residuos

        $vertidoResiduos = Subcategory::where('name', 'Vertido de residuos')->first();
        if ($vertidoResiduos) {
            $vertidoResiduosFields = [
                'tipo_residuo' => ['is_required' => true, 'order_index' => 1, 'default_value' => null],
                'volumen' => ['is_required' => true, 'order_index' => 2, 'default_value' => null],
                'superficie_afectada' => ['is_required' => false, 'order_index' => 3, 'default_value' => null],
                'coste_reposicion' => ['is_required' => false, 'order_index' => 4, 'default_value' => null],
            ];

            foreach ($vertidoResiduosFields as $keyName => $pivotData) {
                if (isset($fields[$keyName])) {
                    $vertidoResiduos->fields()->syncWithoutDetaching([
                        $fields[$keyName] => $pivotData
                    ]);
                }
            }
        }

        // VERTIDOS - Vertido de aguas
    
        $vertidoAguas = Subcategory::where('name', 'Vertido de aguas')->first();
        if ($vertidoAguas) {
            $vertidoAguasFields = [
                'volumen' => ['is_required' => true, 'order_index' => 1, 'default_value' => null],
                'caudal' => ['is_required' => false, 'order_index' => 2, 'default_value' => null],
                'coordenadas_afectacion' => ['is_required' => false, 'order_index' => 3, 'default_value' => null],
            ];

            foreach ($vertidoAguasFields as $keyName => $pivotData) {
                if (isset($fields[$keyName])) {
                    $vertidoAguas->fields()->syncWithoutDetaching([
                        $fields[$keyName] => $pivotData
                    ]);
                }
            }
        }

        // VERTIDOS - Emisiones atmosféricas
 
        $emisionesAtmos = Subcategory::where('name', 'Emisiones atmosféricas')->first();
        if ($emisionesAtmos) {
            $emisionesAtmosFields = [
                'tipo_gas' => ['is_required' => true, 'order_index' => 1, 'default_value' => null],
                'volumen' => ['is_required' => false, 'order_index' => 2, 'default_value' => null],
                'coordenadas_afectacion' => ['is_required' => false, 'order_index' => 3, 'default_value' => null],
            ];

            foreach ($emisionesAtmosFields as $keyName => $pivotData) {
                if (isset($fields[$keyName])) {
                    $emisionesAtmos->fields()->syncWithoutDetaching([
                        $fields[$keyName] => $pivotData
                    ]);
                }
            }
        }
    }
}