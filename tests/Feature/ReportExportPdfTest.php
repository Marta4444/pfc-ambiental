<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Report;
use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Petitioner;

class ReportExportPdfTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_export_a_report_to_pdf()
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
            'title' => 'Informe de prueba para PDF',
            'ip' => '2026-IP001',
        ]);

        $response = $this->get(route('reports.exportPdf', $report));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader('content-disposition');
        $this->assertNotEmpty($response->getContent());
    }

}
