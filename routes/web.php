<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportDetailController;
use App\Http\Controllers\PetitionerController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\SpeciesController;
use App\Http\Controllers\ProtectedAreaController;
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
    Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

    Route::get('subcategories', [SubcategoryController::class, 'index'])->name('subcategories.index');
    Route::get('subcategories/{subcategory}', [SubcategoryController::class, 'show'])->name('subcategories.show');

    // Rutas de asignación de reportes
    Route::post('reports/{report}/self-assign', [ReportController::class, 'selfAssign'])->name('reports.selfAssign');
    Route::post('reports/{report}/assign', [ReportController::class, 'assign'])->name('reports.assign');
    Route::post('reports/{report}/unassign', [ReportController::class, 'unassign'])->name('reports.unassign');

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

    // Rutas de Species (búsqueda para todos los usuarios autenticados)
    Route::get('species/search', [SpeciesController::class, 'search'])->name('species.search');
    Route::get('species/{species}', [SpeciesController::class, 'show'])->name('species.show');
    Route::post('species/check-protection', [SpeciesController::class, 'checkProtection'])->name('species.checkProtection');

    // Rutas de Áreas Protegidas (para usuarios autenticados)
    Route::post('protected-areas/check-coordinates', [ProtectedAreaController::class, 'checkCoordinates'])
        ->name('protected-areas.check-coordinates');
    Route::get('protected-areas/search', [ProtectedAreaController::class, 'search'])
        ->name('protected-areas.search');
});

Route::middleware(['auth', AdminMiddleware::class])->group(function () {
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
    Route::put('fields/{field}/subcategories/{subcategory}', [FieldController::class, 'updateSubcategoryPivot'])
        ->name('fields.updateSubcategoryPivot');

    // Gestión de Species (admin)
    Route::get('species', [SpeciesController::class, 'index'])->name('species.index');
    Route::get('species/{species}/show', [SpeciesController::class, 'adminShow'])->name('species.admin.show');
    Route::get('species/{species}/edit', [SpeciesController::class, 'edit'])->name('species.edit');
    Route::put('species/{species}', [SpeciesController::class, 'update'])->name('species.update');

    // Gestión de áreas protegidas (admin) - CRUD completo
    Route::get('protected-areas', [ProtectedAreaController::class, 'index'])
        ->name('protected-areas.index');
    Route::get('protected-areas/create', [ProtectedAreaController::class, 'create'])
        ->name('protected-areas.create');
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
