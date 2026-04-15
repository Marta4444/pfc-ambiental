<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Mostrar la lista de Categorías.
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
     * Mostrar el formulario para crear una nueva categoría.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Guardar una nueva categoría.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'required|boolean',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Categoria creada con éxito!');
    }

    /**
     * Mostrar una categoría.
     */
    public function show(Category $category)
    {
        return view('categories.show', compact('category'));
    }

    /**
     * Mostrar el formulario para editar una categoría.
     */
    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Actualizar una categoría.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'required|boolean',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Categoria actualizada con éxito!');
    }

    /**
     * Cambiar el atributo Active, solo para Admins
     */
    public function toggleActive(Category $category)
    {
        $category->update(['active' => !$category->active]);

        $status = $category->active ? 'activada' : 'desactivada';
        return redirect()->route('categories.index')->with('success', "Categoría {$status} con éxito!");
    }

    /**
     * Eliminar una categoría.
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Categoría borrada con éxito!');
    }
}
