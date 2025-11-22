<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PetitionerController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; //esta se añade para la autenticación, sobre todo para el Auth::check

Route::get('/', function () {
    if(Auth::check()) {
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
    

});

Route::middleware(['auth', AdminMiddleware::class])->group(function () {
    Route::resource('categories', CategoryController::class)->except(['index', 'show']);
    Route::post('categories/{category}/toggle-active', [CategoryController::class, 'toggleActive'])->name('categories.toggleActive');

    Route::resource('subcategories', SubcategoryController::class)->except(['index', 'show']);
    Route::post('subcategories/{subcategory}/toggle-active', [SubcategoryController::class, 'toggleActive'])->name('subcategories.toggleActive');
    
    // Rutas de gestión de peticionarios (solo admin)
    Route::resource('petitioners', PetitionerController::class)->except(['show']);
    Route::post('petitioners/{petitioner}/toggle-active', [PetitionerController::class, 'toggleActive'])->name('petitioners.toggleActive');
});


require __DIR__.'/auth.php';
