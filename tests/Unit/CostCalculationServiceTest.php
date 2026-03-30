<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Report;
use App\Models\Subcategory;
use App\Services\CostCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function calcula_costes_biodiversidad_correctamente()
    {
        $category = Category::factory()->create(['name' => 'Biodiversidad']);
        $subcategory = Subcategory::factory()->create(['name' => 'Caza furtiva', 'category_id' => $category->id]);
        $report = Report::factory()->create([
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
        ]);
        // Simula detalles del grupo (campos clave para la fórmula)
        $report->details()->createMany([
            ['group_key' => 'species_1', 'field_key' => 'especie', 'value' => 'Test especie'],
            ['group_key' => 'species_1', 'field_key' => 'iucn_category', 'value' => 'EN'], // L=60
            ['group_key' => 'species_1', 'field_key' => 'cites_appendix', 'value' => 'II'], // N=2
            ['group_key' => 'species_1', 'field_key' => 'madurez', 'value' => 'Adulto'], // B=1.5
            ['group_key' => 'species_1', 'field_key' => 'cantidad', 'value' => 2],
            ['group_key' => 'species_1', 'field_key' => 'coste_reposicion', 'value' => 100],
            ['group_key' => 'species_1', 'field_key' => 've', 'value' => 50],
            // IG: ubicacion, nivel trofico, reproduccion, estado vital
            ['group_key' => 'species_1', 'field_key' => 'ubicacion_proteccion', 'value' => 'Espacio protegido'], // 100
            ['group_key' => 'species_1', 'field_key' => 'nivel_trofico', 'value' => 'Terciario (carnívoro)'], // 100
            ['group_key' => 'species_1', 'field_key' => 'reproduccion_cautiverio', 'value' => 'No reproducida en cautiverio'], // 100
            ['group_key' => 'species_1', 'field_key' => 'estado_vital', 'value' => 'Muerto'], // 100
        ]);

        $service = new CostCalculationService();
        $result = $service->calculateForReport($report);

        // VR = [(300 * 60 * 2 * 1.5 * 2) * 2] + 100
        $vrBase = 300 * 60 * 2 * 1.5 * 2;
        $vrTotal = ($vrBase * 2) + 100;
        $veTotal = 50;
        $ig = 1.0; // Todos los componentes a 100
        $vsTotal = $vrTotal * $ig;
        $total = $vrTotal + $veTotal + $vsTotal;

        $this->assertEqualsWithDelta($vrTotal, $result['totals']['VR'], 0.01);
        $this->assertEqualsWithDelta($veTotal, $result['totals']['VE'], 0.01);
        $this->assertEqualsWithDelta($vsTotal, $result['totals']['VS'], 0.01);
        $this->assertEqualsWithDelta($total, $result['totals']['total'], 0.01);
    }

    /** @test */
    public function calcula_costes_infraestructuras_extraccion_aguas_correctamente()
    {
        $category = Category::factory()->create(['name' => 'Infraestructuras']);
        $subcategory = Subcategory::factory()->create(['name' => 'Extracciones de aguas', 'category_id' => $category->id]);
        $report = Report::factory()->create([
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
        ]);
        $report->details()->createMany([
            ['group_key' => 'agua_1', 'field_key' => 'volumen', 'value' => 100],
            ['group_key' => 'agua_1', 'field_key' => 'precio_unitario', 'value' => 2.5],
            ['group_key' => 'agua_1', 'field_key' => 'origen_agua', 'value' => 'Superficial'], // T=1.8
            ['group_key' => 'agua_1', 'field_key' => 'vr_manual', 'value' => 200],
            ['group_key' => 'agua_1', 'field_key' => 'vs_manual', 'value' => 300],
        ]);

        $service = new CostCalculationService();
        $result = $service->calculateForReport($report);

        $veTotal = 100 * 2.5;
        $vrTotal = 200;
        $vsTotal = 300 * 1.8;
        $total = $veTotal + $vrTotal + $vsTotal;

        $this->assertEqualsWithDelta($veTotal, $result['totals']['VE'], 0.01);
        $this->assertEqualsWithDelta($vrTotal, $result['totals']['VR'], 0.01);
        $this->assertEqualsWithDelta($vsTotal, $result['totals']['VS'], 0.01);
        $this->assertEqualsWithDelta($total, $result['totals']['total'], 0.01);
    }

    /** @test */
    public function calcula_costes_vertidos_aguas_correctamente()
    {
        $category = Category::factory()->create(['name' => 'Vertidos']);
        $subcategory = Subcategory::factory()->create(['name' => 'Vertido de aguas', 'category_id' => $category->id]);
        $report = Report::factory()->create([
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
        ]);
        $report->details()->createMany([
            ['group_key' => 'vertido_1', 'field_key' => 'volumen', 'value' => 50],
            ['group_key' => 'vertido_1', 'field_key' => 'coste_limpieza_agua', 'value' => 4],
            ['group_key' => 'vertido_1', 'field_key' => 'vr_manual', 'value' => 100],
            ['group_key' => 'vertido_1', 'field_key' => 'vs_manual', 'value' => 150],
        ]);

        $service = new CostCalculationService();
        $result = $service->calculateForReport($report);

        $veTotal = 50 * 4;
        $vrTotal = 100;
        $vsTotal = 150;
        $total = $veTotal + $vrTotal + $vsTotal;

        $this->assertEqualsWithDelta($veTotal, $result['totals']['VE'], 0.01);
        $this->assertEqualsWithDelta($vrTotal, $result['totals']['VR'], 0.01);
        $this->assertEqualsWithDelta($vsTotal, $result['totals']['VS'], 0.01);
        $this->assertEqualsWithDelta($total, $result['totals']['total'], 0.01);
    }

    /** @test */
    public function calcula_costes_con_cantidad_cero_devuelve_cero()
    {
        $category = Category::factory()->create(['name' => 'Biodiversidad']);
        $subcategory = Subcategory::factory()->create(['name' => 'Caza furtiva', 'category_id' => $category->id]);
        $report = Report::factory()->create([
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
        ]);
        $report->details()->createMany([
            ['group_key' => 'species_1', 'field_key' => 'especie', 'value' => 'Test especie'],
            ['group_key' => 'species_1', 'field_key' => 'iucn_category', 'value' => 'EN'],
            ['group_key' => 'species_1', 'field_key' => 'cites_appendix', 'value' => 'II'],
            ['group_key' => 'species_1', 'field_key' => 'madurez', 'value' => 'Adulto'],
            ['group_key' => 'species_1', 'field_key' => 'cantidad', 'value' => 0],
            ['group_key' => 'species_1', 'field_key' => 'coste_reposicion', 'value' => 0],
            ['group_key' => 'species_1', 'field_key' => 've', 'value' => 0],
        ]);
        $service = new CostCalculationService();
        $result = $service->calculateForReport($report);
        $this->assertEquals(0, $result['totals']['VR']);
        $this->assertEquals(0, $result['totals']['VE']);
        $this->assertEquals(0, $result['totals']['VS']);
        $this->assertEquals(0, $result['totals']['total']);
    }

    /** @test */
    public function calcula_costes_con_valores_negativos_trata_como_cero()
    {
        $category = Category::factory()->create(['name' => 'Infraestructuras']);
        $subcategory = Subcategory::factory()->create(['name' => 'Extracciones de aguas', 'category_id' => $category->id]);
        $report = Report::factory()->create([
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
        ]);
        $report->details()->createMany([
            ['group_key' => 'agua_1', 'field_key' => 'volumen', 'value' => -100],
            ['group_key' => 'agua_1', 'field_key' => 'precio_unitario', 'value' => -2.5],
            ['group_key' => 'agua_1', 'field_key' => 'origen_agua', 'value' => 'Superficial'],
            ['group_key' => 'agua_1', 'field_key' => 'vr_manual', 'value' => -200],
            ['group_key' => 'agua_1', 'field_key' => 'vs_manual', 'value' => -300],
        ]);
        $service = new CostCalculationService();
        $result = $service->calculateForReport($report);
        // Esperado: los negativos se tratan como 0
        $this->assertEqualsWithDelta(0, $result['totals']['VE'], 0.01);
        $this->assertEqualsWithDelta(0, $result['totals']['VR'], 0.01);
        $this->assertEqualsWithDelta(0, $result['totals']['VS'], 0.01);
        $this->assertEqualsWithDelta(0, $result['totals']['total'], 0.01);
    }

    /** @test */
    public function calcula_costes_con_campos_obligatorios_ausentes_usa_valores_por_defecto()
    {
        $category = Category::factory()->create(['name' => 'Vertidos']);
        $subcategory = Subcategory::factory()->create(['name' => 'Vertido de aguas', 'category_id' => $category->id]);
        $report = Report::factory()->create([
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
        ]);
        // Solo se da el grupo, sin detalles relevantes
        $report->details()->create([
            'group_key' => 'vertido_1',
            'field_key' => 'dummy',
            'value' => null,
        ]);
        $service = new CostCalculationService();
        $result = $service->calculateForReport($report);
        // Todos los costes deberían ser 0
        $this->assertEquals(0, $result['totals']['VE']);
        $this->assertEquals(0, $result['totals']['VR']);
        $this->assertEquals(0, $result['totals']['VS']);
        $this->assertEquals(0, $result['totals']['total']);
    }
}
