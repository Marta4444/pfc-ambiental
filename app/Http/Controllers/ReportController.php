<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Category;
use App\Models\Subcategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /*$reports = Report::with(['category', 'subcategory'])
        ->where('user_id', Auth::id())
        ->latest()
        ->get();

        return view('reports.index', compact('reports'));*/

        $user = Auth::user();

        // Modificado para que si es admin vea todos los informes, si no, solo los propios, y que muestre 10 por página.
        if ($user && $user->role === 'admin') {
            $reports = Report::with(['category', 'subcategory', 'user'])->latest()->paginate(10);
        } else {
            $reports = Report::with(['category', 'subcategory'])->where('user_id', Auth::id())->latest()->paginate(10);
        }

        return view('reports.index', compact('reports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('active', true)->get();
        $subcategories = Subcategory::where('active', true)->get(); 
        return view('reports.create', compact('categories', 'subcategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'location' => 'nullable|string|max:255',
            'coordinates' => 'nullable|string|max:100',
            'date_damage' => 'required|date',
            'affected_area' => 'nullable|numeric',
            'criticallity' => 'nullable|integer|min:1|max:5',
            'status' => 'nullable|string|in:pendiente,en_proceso,cerrado,procesando,resuelto',
            'pdf_report' => 'nullable|file|mimes:pdf|max:20480',
        ]);

        // comprobar coherencia subcategory -> category
        $belongs = Subcategory::where('id', $validated['subcategory_id'])
            ->where('category_id', $validated['category_id'])
            ->exists();

        if (! $belongs) {
            return back()
                ->withErrors(['subcategory_id' => 'La subcategoría no pertenece a la categoría seleccionada.'])
                ->withInput();
        }

        $path = null;
        if ($request->hasFile('pdf_report')) {
            $path = $request->file('pdf_report')->store('reports', 'public');
        }

        $report = Report::create([
            'user_id' => Auth::id(),
            'category_id' => $validated['category_id'],
            'subcategory_id' => $validated['subcategory_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'location' => $validated['location'] ?? null,
            'coordinates' => $validated['coordinates'] ?? null,
            'date_damage' => $validated['date_damage'],
            'affected_area' => $validated['affected_area'] ?? null,
            'criticallity' => $validated['criticallity'] ?? 1,
            'status' => $validated['status'] ?? 'pendiente',
            'pdf_report' => $path,
        ]);

        return redirect()->route('reports.show', $report)->with('success', 'Informe creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        $report->load(['category', 'subcategory', 'user']);
        return view('reports.show', compact('report'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        if (Auth::id() !== $report->user_id && Auth::user()->role !== 'admin') {
            return redirect()->route('reports.show', $report)->with('error', 'No tienes permiso para editar este informe.');
        }

        // cargar colecciones para los selects
        $categories = Category::where('active', true)->get();
        $subcategories = Subcategory::where('active', true)->get();

        return view('reports.edit', compact('report', 'categories', 'subcategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->role === 'admin';
        $completed = $report->status === 'resuelto';

        if (! $isAdmin && $completed) {
            return redirect()->route('reports.show', $report)->with('error', 'El informe ya está resuelto y no puede ser modificado.');
        }

        if ($isAdmin) {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'subcategory_id' => 'required|exists:subcategories,id',
                'location' => 'nullable|string|max:255',
                'coordinates' => 'nullable|string|max:100',
                'date_damage' => 'required|date',
                'affected_area' => 'nullable|numeric',
                'criticallity' => 'nullable|integer|min:1|max:5',
                'status' => 'required|in:pendiente,procesando,resuelto',
                'pdf_report' => 'nullable|file|mimes:pdf|max:20480',
            ]);

            // comprobar coherencia subcategory -> category
            $belongs = Subcategory::where('id', $validated['subcategory_id'])
                ->where('category_id', $validated['category_id'])
                ->exists();

            if (! $belongs) {
                return back()->withErrors(['subcategory_id' => 'La subcategoría no pertenece a la categoría seleccionada.'])->withInput();
            }

            if ($request->hasFile('pdf_report')) {
                // borrar antiguo si existe
                if ($report->pdf_report) {
                    Storage::disk('public')->delete($report->pdf_report);
                }
                $validated['pdf_report'] = $request->file('pdf_report')->store('reports', 'public');
            }

            $report->update($validated);
        } else {
            // usuario normal: solo puede cambiar el estado mientras no esté resuelto
            $validated = $request->validate([
                'status' => 'required|in:pendiente,procesando,resuelto',
            ]);

            $report->status = $validated['status'];
            $report->save();
        }

        return redirect()->route('reports.show', $report)->with('success', 'Informe actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        $user = Auth::user();

        //Permitir borrar solo al admin o al autor del informe
        if (! $user || ($user->role !== 'admin' && $user->id !== $report->user_id)) {
            return redirect()->route('reports.show', $report)
                ->with('error', 'No tienes permiso para eliminar este informe.');
        }

        //Borrar archivo adjunto si existe
        if ($report->pdf_report) {
            Storage::disk('public')->delete($report->pdf_report);
        }

        $report->delete();

        return redirect()->route('reports.index')->with('success', 'Informe eliminado correctamente.');
    }
}
