<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Report;
use App\Models\User;

class ReportCreationTest extends TestCase
{
    use RefreshDatabase;


    /** @test */
    public function it_creates_a_report_with_valid_data()
    {
        $user = User::factory()->create();
        $category = \App\Models\Category::factory()->create();
        $subcategory = \App\Models\Subcategory::factory()->create(['category_id' => $category->id]);
        $petitioner = \App\Models\Petitioner::factory()->create();
        $this->actingAs($user);

        $uniqueIp = '2026-IP' . random_int(100, 999);
        $data = [
            'ip' => $uniqueIp,
            'title' => 'Incendio en bosque',
            'background' => 'Incendio forestal en la zona norte.',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'community' => 'Andalucía',
            'province' => 'Sevilla',
            'locality' => 'Sevilla',
            'coordinates' => '37.3886,-5.9823', 
            'petitioner_id' => $petitioner->id,
            'petitioner_other' => null,
            'office' => 'Despacho Central',
            'diligency' => 'D-2026-001',
            'urgency' => 'alta',
            'date_petition' => '2026-03-30',
            'date_damage' => '2026-03-29',
            'assigned_to' => null,
            'vr_total' => 1000.50,
            've_total' => 500.25,
            'vs_total' => 200.00,
            'total_cost' => 1700.75,
        ];

        $response = $this->post('/reports', $data);
        $response->assertStatus(302); // Redirección tras éxito


        $this->assertDatabaseHas('reports', [
            'ip' => $uniqueIp,
            'title' => 'Incendio en bosque',
            'background' => 'Incendio forestal en la zona norte.',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'community' => 'Andalucía',
            'province' => 'Sevilla',
            'locality' => 'Sevilla',
            'coordinates' => '37.3886,-5.9823',
            'petitioner_id' => $petitioner->id,
            'petitioner_other' => null,
            'office' => 'Despacho Central',
            'diligency' => 'D-2026-001',
            'urgency' => 'alta',
            'date_petition' => '2026-03-30 00:00:00',
            'date_damage' => '2026-03-29 00:00:00',
            'assigned_to' => null,
            'vr_total' => 1000.50,
            've_total' => 500.25,
            'vs_total' => 200.00,
            'total_cost' => 1700.75,
        ]);
    }


    /** @test */
    public function it_fails_to_create_report_with_missing_required_fields()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            // Todos los campos requeridos faltan
        ];

        $response = $this->post('/reports', $data);
        $response->assertSessionHasErrors([
            'ip', 'title', 'background', 'category_id', 'subcategory_id', 'community', 'province', 'locality', 'petitioner_id', 'urgency', 'date_petition', 'date_damage'
        ]);
    }

    /** @test */
    public function it_fails_with_invalid_ip_format()
    {
        $user = User::factory()->create();
        $category = \App\Models\Category::factory()->create();
        $subcategory = \App\Models\Subcategory::factory()->create(['category_id' => $category->id]);
        $petitioner = \App\Models\Petitioner::factory()->create();
        $this->actingAs($user);

        $data = [
            'ip' => 'BAD-IP',
            'title' => 'Test',
            'background' => 'Test',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'community' => 'Andalucía',
            'province' => 'Sevilla',
            'locality' => 'Sevilla',
            'petitioner_id' => $petitioner->id,
            'urgency' => 'normal',
            'date_petition' => '2026-03-30',
            'date_damage' => '2026-03-29',
        ];

        $response = $this->post('/reports', $data);
        $response->assertSessionHasErrors(['ip']);
    }

    /** @test */
    public function it_fails_with_duplicate_ip()
    {
        $user = User::factory()->create();
        $category = \App\Models\Category::factory()->create();
        $subcategory = \App\Models\Subcategory::factory()->create(['category_id' => $category->id]);
        $petitioner = \App\Models\Petitioner::factory()->create();
        $this->actingAs($user);

        $existing = \App\Models\Report::factory()->create(['ip' => '2026-IP999']);

        $data = [
            'ip' => '2026-IP999',
            'title' => 'Test',
            'background' => 'Test',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'community' => 'Andalucía',
            'province' => 'Sevilla',
            'locality' => 'Sevilla',
            'petitioner_id' => $petitioner->id,
            'urgency' => 'normal',
            'date_petition' => '2026-03-30',
            'date_damage' => '2026-03-29',
        ];

        $response = $this->post('/reports', $data);
        $response->assertSessionHasErrors(['ip']);
    }

    /** @test */
    public function it_fails_with_invalid_urgency()
    {
        $user = User::factory()->create();
        $category = \App\Models\Category::factory()->create();
        $subcategory = \App\Models\Subcategory::factory()->create(['category_id' => $category->id]);
        $petitioner = \App\Models\Petitioner::factory()->create();
        $this->actingAs($user);

        $data = [
            'ip' => '2026-IP123',
            'title' => 'Test',
            'background' => 'Test',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'community' => 'Andalucía',
            'province' => 'Sevilla',
            'locality' => 'Sevilla',
            'petitioner_id' => $petitioner->id,
            'urgency' => 'super-urgente',
            'date_petition' => '2026-03-30',
            'date_damage' => '2026-03-29',
        ];

        $response = $this->post('/reports', $data);
        $response->assertSessionHasErrors(['urgency']);
    }


    /** @test */
    public function it_saves_all_report_data_correctly()
    {
        $user = User::factory()->create();
        $category = \App\Models\Category::factory()->create();
        $subcategory = \App\Models\Subcategory::factory()->create(['category_id' => $category->id]);
        $petitioner = \App\Models\Petitioner::factory()->create();
        $assignedUser = User::factory()->create();
        $this->actingAs($user);

        $uniqueIp = '2026-IP' . random_int(100, 999);
        $data = [
            'ip' => $uniqueIp,
            'title' => 'Vertido químico',
            'background' => 'Vertido en el río principal.',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'community' => 'Andalucía', 
            'province' => 'Sevilla',    
            'locality' => 'Sevilla',    
            'coordinates' => '37.3886,-5.9823', 
            'petitioner_id' => $petitioner->id,
            'petitioner_other' => null,
            'office' => 'Oficina Norte',
            'diligency' => 'D-2026-777',
            'urgency' => 'urgente',
            'date_petition' => '2026-03-28',
            'date_damage' => '2026-03-27',
            'assigned_to' => $assignedUser->id,
            'vr_total' => 2000.00,
            've_total' => 1000.00,
            'vs_total' => 500.00,
            'total_cost' => 3500.00,
        ];

        $response = $this->post('/reports', $data);


        $this->assertDatabaseHas('reports', [
            'ip' => $uniqueIp,
            'title' => 'Vertido químico',
            'background' => 'Vertido en el río principal.',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'community' => 'Andalucía',
            'province' => 'Sevilla',
            'locality' => 'Sevilla',
            'petitioner_id' => $petitioner->id,
            'office' => 'Oficina Norte',
            'diligency' => 'D-2026-777',
            'urgency' => 'urgente',
            'date_petition' => '2026-03-28 00:00:00',
            'date_damage' => '2026-03-27 00:00:00',
            'assigned_to' => $assignedUser->id,
            'vr_total' => 2000.00,
            've_total' => 1000.00,
            'vs_total' => 500.00,
            'total_cost' => 3500.00,
        ]);
    }
}
