<?php

namespace App\Http\Controllers;

use App\Models\Subcategory;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; //Para autenticación de Admin y user

class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Solo los admin pueden crear subcategorías
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('subcategories.index')->with('error', 'No tienes permisos para crear subcategorías. Contacta con un Administrador.');
        }

        $categories = Category::where('active', true)->get();

        return view('subcategories.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Solo los admin pueden crear subcategorías
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('subcategories.index')->with('error', 'No tienes permisos para crear subcategorías. Contacta con un Administrador.');
        }

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
     * Display the specified resource.
     */
    public function show(Subcategory $subcategory)
    {
        $subcategory->load('category');
        return view('subcategories.show', compact('subcategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subcategory $subcategory)
    {
        // Solo los admin pueden editar subcategorías
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('subcategories.show', $subcategory)->with('error', 'No tienes permisos para editar subcategorías. Contacta con un Administrador.');
        }

        $categories = Category::where('active', true)->get();

        return view('subcategories.edit', compact('subcategory', 'categories'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subcategory $subcategory)
    {
        // Solo los admin pueden actualizar subcategorías
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('subcategories.show', $subcategory)->with('error', 'No tienes permisos para editar subcategorías. Contacta con un Administrador.');
        }

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
     * Change active status, only for Admins
     */
    public function toggleActive(Subcategory $subcategory)
    {
        // Solo los admin pueden activar o desactivar subcategorías
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('subcategories.index')->with('error', 'No tienes permisos para cambiar el estado de subcategorías. Contacta con un Administrador.');
        }

        $subcategory->update(['active' => !$subcategory->active]);

        $status = $subcategory->active ? 'activada' : 'desactivada';
        return redirect()->route('subcategories.index')->with('success', "Subcategoría {$status} con éxito!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subcategory $subcategory)
    {
        // Solo los admin pueden eliminar subcategorías
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('subcategories.index')->with('error', 'No tienes permisos para eliminar subcategorías. Contacta con un Administrador.');
        }

        $subcategory->delete();

        return redirect()->route('subcategories.index')->with('success', 'Subcategoría eliminada con éxito!');
    }
}
