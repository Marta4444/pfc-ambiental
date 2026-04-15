<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportDetailController;
use App\Http\Controllers\ReportCostItemController;
use App\Http\Controllers\PetitionerController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\SpeciesController;
use App\Http\Controllers\ProtectedAreaController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\SpeciesAdminController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\UserAdminController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show')->whereNumber('category');

    Route::get('subcategories', [SubcategoryController::class, 'index'])->name('subcategories.index');
    Route::get('subcategories/{subcategory}', [SubcategoryController::class, 'show'])->name('subcategories.show')->whereNumber('subcategory');

    // Rutas de asignación de reportes
    Route::post('reports/{report}/self-assign', [ReportController::class, 'selfAssign'])->name('reports.selfAssign');
    Route::post('reports/{report}/assign', [ReportController::class, 'assign'])->name('reports.assign');
    Route::post('reports/{report}/unassign', [ReportController::class, 'unassign'])->name('reports.unassign');
    
    // Rutas para finalizar y reabrir reportes
    Route::post('reports/{report}/finalize', [ReportController::class, 'finalize'])->name('reports.finalize');
    Route::post('reports/{report}/reopen', [ReportController::class, 'reopen'])->name('reports.reopen');
    
    // Ruta para exportar a PDF
    Route::get('reports/{report}/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.exportPdf');

    // Ruta para descargar el PDF adjunto
    Route::get('reports/{report}/download-attachment', [ReportController::class, 'downloadAttachment'])->name('reports.downloadAttachment');

    Route::resource('reports', ReportController::class);

    // Report Details
    Route::prefix('reports/{report}/details')->name('report-details.')->group(function () {
        Route::get('/', [ReportDetailController::class, 'index'])->name('index');
        Route::get('/create', [ReportDetailController::class, 'create'])->name('create');
        Route::post('/', [ReportDetailController::class, 'store'])->name('store');
        Route::get('/{groupKey}', [ReportDetailController::class, 'show'])->name('show');
        Route::get('/{groupKey}/edit', [ReportDetailController::class, 'edit'])->name('edit');
        Route::put('/{groupKey}', [ReportDetailController::class, 'update'])->name('update');
        Route::delete('/{groupKey}', [ReportDetailController::class, 'destroy'])->name('destroy');
    });

    // Report Cost Items
    Route::prefix('reports/{report}/costs')->name('report-costs.')->group(function () {
        Route::get('/', [ReportCostItemController::class, 'index'])->name('index');
        Route::post('/calculate', [ReportCostItemController::class, 'calculate'])->name('calculate');
        Route::delete('/', [ReportCostItemController::class, 'destroy'])->name('destroy');
    });

    // Rutas de Species (búsqueda para todos los usuarios autenticados)
    Route::get('species/search', [SpeciesController::class, 'search'])->name('species.search');

    // Rutas de Áreas Protegidas (para usuarios autenticados)
    Route::post('protected-areas/check-coordinates', [ProtectedAreaController::class, 'checkCoordinates'])
        ->name('protected-areas.check-coordinates');
    Route::get('protected-areas/search', [ProtectedAreaController::class, 'search'])
        ->name('protected-areas.search');

    // Estadísticas (para todos los usuarios autenticados)
    Route::get('statistics', [StatisticsController::class, 'index'])->name('statistics.index');
});

Route::middleware(['auth', AdminMiddleware::class])->group(function () {
    // Gestión de usuarios (solo admin)
    Route::get('admin/users', [UserAdminController::class, 'index'])->name('admin.users.index');
    Route::get('admin/users/create', [UserAdminController::class, 'create'])->name('admin.users.create');
    Route::post('admin/users', [UserAdminController::class, 'store'])->name('admin.users.store');
    Route::get('admin/users/{user}/edit', [UserAdminController::class, 'edit'])->name('admin.users.edit');
    Route::put('admin/users/{user}', [UserAdminController::class, 'update'])->name('admin.users.update');
    Route::post('admin/users/{user}/toggle-active', [UserAdminController::class, 'toggleActive'])->name('admin.users.toggle-active');

    // Auditoría (solo admin)
    Route::get('audit', [AuditLogController::class, 'index'])->name('audit.index');
    Route::get('audit/{auditLog}', [AuditLogController::class, 'show'])->name('audit.show');

    // Estadísticas de administración (solo admin)
    Route::get('statistics/admin', [StatisticsController::class, 'admin'])->name('statistics.admin');

    Route::resource('categories', CategoryController::class)->except(['index', 'show']);
    Route::post('categories/{category}/toggle-active', [CategoryController::class, 'toggleActive'])->name('categories.toggleActive');

    Route::resource('subcategories', SubcategoryController::class)->except(['index', 'show']);
    Route::post('subcategories/{subcategory}/toggle-active', [SubcategoryController::class, 'toggleActive'])->name('subcategories.toggleActive');

    // Rutas de gestión de peticionarios (solo admin)
    Route::resource('petitioners', PetitionerController::class);
    Route::post('petitioners/{petitioner}/toggle-active', [PetitionerController::class, 'toggleActive'])->name('petitioners.toggleActive');

    // Rutas para gestión de campos de subcategorías
    Route::resource('fields', FieldController::class);
    Route::post('fields/{field}/toggle-active', [FieldController::class, 'toggleActive'])
        ->name('fields.toggleActive');
    Route::post('fields/{field}/assign', [FieldController::class, 'assignToSubcategory'])
        ->name('fields.assignToSubcategory');
    Route::delete('fields/{field}/subcategories/{subcategory}', [FieldController::class, 'unassignFromSubcategory'])
        ->name('fields.unassignFromSubcategory');

    // Administración de especies con sincronización API
    Route::prefix('admin/species')->name('admin.species.')->group(function () {
        Route::get('/', [SpeciesAdminController::class, 'index'])->name('index');
        Route::get('/create', [SpeciesAdminController::class, 'create'])->name('create');
        Route::post('/', [SpeciesAdminController::class, 'store'])->name('store');
        Route::get('/{species}/edit', [SpeciesAdminController::class, 'edit'])->name('edit');
        Route::put('/{species}', [SpeciesAdminController::class, 'update'])->name('update');
        Route::delete('/{species}', [SpeciesAdminController::class, 'destroy'])->name('destroy');
        Route::post('/{species}/sync', [SpeciesAdminController::class, 'sync'])->name('sync');
        Route::post('/sync-all', [SpeciesAdminController::class, 'syncAll'])->name('syncAll');
        Route::get('/search-api', [SpeciesAdminController::class, 'search'])->name('search');
        Route::post('/import-spanish', [SpeciesAdminController::class, 'importSpanish'])->name('importSpanish');
        Route::get('/logs', [SpeciesAdminController::class, 'logs'])->name('logs');
        Route::get('/export', [SpeciesAdminController::class, 'export'])->name('export');
    });

    // Gestión de áreas protegidas (admin) - CRUD completo
    Route::get('protected-areas', [ProtectedAreaController::class, 'index'])
        ->name('protected-areas.index');
    Route::get('protected-areas/create', [ProtectedAreaController::class, 'create'])
        ->name('protected-areas.create');
    Route::get('protected-areas/check', [ProtectedAreaController::class, 'checkTool'])
        ->name('protected-areas.check');
    Route::post('protected-areas', [ProtectedAreaController::class, 'store'])
        ->name('protected-areas.store');
    Route::get('protected-areas/{protectedArea}', [ProtectedAreaController::class, 'show'])
        ->name('protected-areas.show');
    Route::get('protected-areas/{protectedArea}/edit', [ProtectedAreaController::class, 'edit'])
        ->name('protected-areas.edit');
    Route::put('protected-areas/{protectedArea}', [ProtectedAreaController::class, 'update'])
        ->name('protected-areas.update');
    Route::delete('protected-areas/{protectedArea}', [ProtectedAreaController::class, 'destroy'])
        ->name('protected-areas.destroy');
});

require __DIR__ . '/auth.php';
