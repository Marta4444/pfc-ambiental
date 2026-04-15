<?php

namespace App\Http\Controllers;

use App\Models\Subcategory;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    /**
     * Mostrar todas las subcategorías.
     */
    public function index(Request $request)
    {
        $query = Subcategory::with('category');

        // Filtrar por estado activo si se manda esta info
        if ($request->has('active') && $request->active !== '') {
            $query->where('active', $request->active === '1');
        }

        $subcategories = $query->orderBy('category_id')->orderBy('name')->get();

        return view('subcategories.index', compact('subcategories'));
    }

    /**
     * Mostrar el formulario para crear una nueva subcategoría.
     */
    public function create()
    {
        $categories = Category::where('active', true)->get();

        return view('subcategories.create', compact('categories'));
    }

    /**
     * Guardar una nueva subcategoría en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'active' => 'required|boolean',
        ]);

        Subcategory::create($validated);

        return redirect()->route('subcategories.index')->with('success', 'Subcategoría creada con éxito!');
    }

    /**
     * Mostrar una subcategoría específica.
     */
    public function show(Subcategory $subcategory)
    {
        $subcategory->load('category');
        return view('subcategories.show', compact('subcategory'));
    }

    /**
     * Mostrar el formulario para editar una subcategoría específica.
     */
    public function edit(Subcategory $subcategory)
    {
        $categories = Category::where('active', true)->get();

        return view('subcategories.edit', compact('subcategory', 'categories'));

    }

    /**
     * Actualizar una subcategoría específica en la base de datos.
     */
    public function update(Request $request, Subcategory $subcategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'active' => 'required|boolean',
        ]);

        $subcategory->update($validated);

        return redirect()->route('subcategories.index')->with('success', 'Subcategoría actualizada con éxito!');
    }

    /**
     * Cambiar el estado activo, solo para administradores
     */
    public function toggleActive(Subcategory $subcategory)
    {
        $subcategory->update(['active' => !$subcategory->active]);

        $status = $subcategory->active ? 'activada' : 'desactivada';
        return redirect()->route('subcategories.index')->with('success', "Subcategoría {$status} con éxito!");
    }

    /**
     * Eliminar una subcategoría específica.
     */
    public function destroy(Subcategory $subcategory)
    {
        $subcategory->delete();

        return redirect()->route('subcategories.index')->with('success', 'Subcategoría eliminada con éxito!');
    }
}
