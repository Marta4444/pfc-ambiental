<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Category::query();

        // Filtrar por estado activo si se proporciona
        if ($request->has('active') && $request->active !== '') {
            $query->where('active', $request->active === '1');
        }

        $categories = $query->orderBy('name')->get();

        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Solo los admin pueden crear nuevas categorías
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('categories.index')->with('error', 'No tienes permisos para crear categorías. Contacta con un administrador.');
        }

        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // Solo los admin pueden crear nuevas categorías
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('categories.index')->with('error', 'No tienes permisos para crear categorías. Contacta con un Administrador');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'required|boolean',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Categoria creada con éxito!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {

        // Solo los admin pueden editar las categorías
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('categories.show', $category)->with('error', 'No tienes permisos para editar categorías. Contacta con un Administrador.');
        }
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        // Solo los admin pueden actualizar categorías, cualquier atributo.
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('categories.show', $category)->with('error', 'No tienes permisos para editar categorías.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'required|boolean',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Categoria actualizada con éxito!');
    }

    /**
     * Change Active atrtibute, only for Admins
     */
    public function toggleActive(Category $category)
    {
        // Solo los admin pueden activar o desactivar categorías
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('categories.index')->with('error', 'No tienes permisos para cambiar el estado de categorías. Contacta con un Administrador.');
        }

        $category->update(['active' => !$category->active]);

        $status = $category->active ? 'activada' : 'desactivada';
        return redirect()->route('categories.index')->with('success', "Categoría {$status} con éxito!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Solo los admin pueden eliminar categorías
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('categories.index')->with('error', 'No tienes permisos para eliminar categorías.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Categoría borrada con éxito!');
    }
}
