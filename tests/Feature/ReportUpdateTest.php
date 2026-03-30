<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Report;
use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Petitioner;

class ReportUpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_updates_a_report_with_valid_data()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create(['category_id' => $category->id]);
        $petitioner = Petitioner::factory()->create();
        $this->actingAs($user);

        $report = Report::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'petitioner_id' => $petitioner->id,
            'community' => 'Andalucía',
            'province' => 'Sevilla',
            'locality' => 'Sevilla',
            'coordinates' => '37.3886,-5.9823',
            'title' => 'Título original',
            'background' => 'Antecedentes originales',
            'office' => 'Despacho Central',
            'diligency' => 'D-2026-477',
            'urgency' => 'normal',
            'date_petition' => '2026-03-05 14:10:09',
            'date_damage' => '2026-02-18 02:35:49',
        ]);

        $updateData = [
            // Solo campos editables por usuario normal
            'title' => 'Título actualizado',
            'background' => 'Antecedentes actualizados',
            'community' => 'Andalucía',
            'province' => 'Sevilla',
            'locality' => 'Sevilla',
            'coordinates' => '37.3886,-5.9823',
            'petitioner_id' => $petitioner->id,
            'petitioner_other' => null,
            'office' => 'Oficina Norte',
            'diligency' => 'D-2026-999',
            'urgency' => 'urgente',
            'date_petition' => '2026-03-30',
            'date_damage' => '2026-03-29',
        ];

        $response = $this->put("/reports/{$report->id}", $updateData);
        $response->assertStatus(302);

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'title' => 'Título actualizado',
            'background' => 'Antecedentes actualizados',
            'office' => 'Oficina Norte',
            'diligency' => 'D-2026-999',
            'urgency' => 'urgente',
            'date_petition' => '2026-03-30 00:00:00',
            'date_damage' => '2026-03-29 00:00:00',
        ]);
    }


    /** @test */
    public function it_fails_to_update_report_with_invalid_coordinates()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create(['category_id' => $category->id]);
        $petitioner = Petitioner::factory()->create();
        $this->actingAs($user);

        // Crear el reporte con todos los campos obligatorios
        $report = Report::factory()->create([
            'user_id' => $user->id,
            'ip' => '2026-IP001',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'petitioner_id' => $petitioner->id,
            'community' => 'Andalucía',
            'province' => 'Sevilla',
            'locality' => 'Sevilla',
            'coordinates' => '37.3886,-5.9823',
            'status' => 'nuevo',
        ]);

        $updateData = [
            'title' => 'Título actualizado',
            'background' => 'Antecedentes actualizados',
            'community' => 'Andalucía',
            'province' => 'Sevilla',
            'locality' => 'Sevilla',
            'coordinates' => '99.9999,99.9999', // Inválidas
            'petitioner_id' => $petitioner->id,
            'petitioner_other' => null,
            'office' => 'Oficina Norte',
            'diligency' => 'D-2026-999',
            'urgency' => 'urgente',
            'date_petition' => '2026-03-30',
            'date_damage' => '2026-03-29',
        ];

        $response = $this->put("/reports/{$report->id}", $updateData);
        $response->assertSessionHasErrors(['coordinates']);

        // Verifica que los datos editables enviados se mantienen en la sesión (old input)
        $this->assertEquals('Título actualizado', session('_old_input')['title'] ?? null);
        $this->assertEquals('99.9999,99.9999', session('_old_input')['coordinates'] ?? null);
    }
}
