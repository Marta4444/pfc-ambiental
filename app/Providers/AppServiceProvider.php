<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Field;
use App\Models\Petitioner;
use App\Models\ProtectedArea;
use App\Models\Report;
use App\Models\ReportCostItem;
use App\Models\ReportDetail;
use App\Models\Species;
use App\Models\Subcategory;
use App\Observers\AuditObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar el observer de auditoría para todos los modelos auditables
        $auditableModels = [
            Report::class,
            ReportDetail::class,
            ReportCostItem::class,
            Category::class,
            Subcategory::class,
            Field::class,
            Petitioner::class,
            Species::class,
            ProtectedArea::class,
        ];

        foreach ($auditableModels as $model) {
            $model::observe(AuditObserver::class);
        }
    }
}