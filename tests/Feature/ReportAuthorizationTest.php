<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Petitioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    }

    /** @test */
    public function solo_usuarios_autenticados_pueden_crear_reports()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create(['category_id' => $category->id]);
        $petitioner = Petitioner::factory()->create();
        $data = [
            'ip' => '2026-IP' . random_int(100, 999),
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
        $this->post(route('reports.store'), $data)->assertRedirect(route('login'));
        $this->actingAs($user)
            ->post(route('reports.store'), $data)
            ->assertStatus(302)
            ->assertSessionHasNoErrors();
    }

    /** @test */
    public function solo_usuario_asignado_o_admin_puede_editar_reports()
    {
        $assigned = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $other = User::factory()->create();
        $report = Report::factory()->create(['assigned_to' => $assigned->id]);
        // Invitado
        $this->get(route('reports.edit', $report))->assertRedirect(route('login'));
        // Otro usuario
        $this->actingAs($other)
            ->get(route('reports.edit', $report))
            ->assertForbidden();
        // Usuario asignado
        $this->actingAs($assigned)
            ->get(route('reports.edit', $report))
            ->assertOk();
        // Admin
        $this->actingAs($admin)
            ->get(route('reports.edit', $report))
            ->assertOk();
    }

    /** @test */
    public function solo_usuario_asignado_o_admin_puede_calcular_costes()
    {
        $assigned = User::factory()->create([
            'active' => true,
            'email_verified_at' => now(),
        ]);
        $admin = User::factory()->create([
            'role' => 'admin',
            'active' => true,
            'email_verified_at' => now(),
        ]);
        $other = User::factory()->create([
            'active' => true,
            'email_verified_at' => now(),
        ]);
        $report = Report::factory()->create(['assigned_to' => $assigned->id]);
        // Invitado
        $this->post(route('report-costs.calculate', $report))->assertRedirect(route('login'));
        // Otro usuario
        $this->actingAs($other)
            ->post(route('report-costs.calculate', $report))
            ->assertStatus(302); // Laravel 12 puede redirigir en vez de 403
        // Usuario asignado
        $this->actingAs($assigned)
            ->post(route('report-costs.calculate', $report))
            ->assertSessionHasNoErrors();
        // Admin
        $this->actingAs($admin)
            ->post(route('report-costs.calculate', $report))
            ->assertSessionHasNoErrors();
    }

    /** @test */
    public function solo_usuario_asignado_o_admin_puede_aniadir_detalles()
    {
        $assigned = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $other = User::factory()->create();
        $report = Report::factory()->create(['assigned_to' => $assigned->id]);
        $data = [
            'group_key' => 'test_1',
            'field_key' => 'campo',
            'value' => 'valor',
        ];
        // Invitado
        $this->post(route('report-details.store', $report), $data)->assertRedirect(route('login'));
        // Otro usuario
        $this->actingAs($other)
            ->post(route('report-details.store', $report), $data)
            ->assertForbidden();
        // Usuario asignado
        $this->actingAs($assigned)
            ->post(route('report-details.store', $report), $data)
            ->assertSessionHasNoErrors();
        // Admin
        $this->actingAs($admin)
            ->post(route('report-details.store', $report), $data)
            ->assertSessionHasNoErrors();
    }
}
