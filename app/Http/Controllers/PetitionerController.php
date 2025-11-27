<?php

namespace App\Http\Controllers;

use App\Models\Petitioner;
use Illuminate\Http\Request;

class PetitionerController extends Controller
{
    public function index()
    {
        $petitioners = Petitioner::orderBy('order')->paginate(10);
        return view('petitioners.index', compact('petitioners'));
    }

    public function create()
    {
        return view('petitioners.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:petitioners,name',
            'active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        Petitioner::create($validated);

        return redirect()->route('petitioners.index')->with('success', 'Peticionario creado correctamente.');
    }

    public function show(Petitioner $petitioner)
    {
        $petitioner->load(['reports' => function ($query) {
            $query->latest()->take(10);
        }]);
        
        return view('petitioners.show', compact('petitioner'));
    }

    public function edit(Petitioner $petitioner)
    {
        return view('petitioners.edit', compact('petitioner'));
    }

    public function update(Request $request, Petitioner $petitioner)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:petitioners,name,' . $petitioner->id,
            'active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        $petitioner->update($validated);

        return redirect()->route('petitioners.index')->with('success', 'Peticionario actualizado correctamente.');
    }

    public function destroy(Petitioner $petitioner)
    {
        if ($petitioner->reports()->count() > 0) {
            return redirect()->route('petitioners.index')
                ->with('error', 'No se puede eliminar este peticionario porque tiene casos asociados.');
        }

        $petitioner->delete();

        return redirect()->route('petitioners.index')->with('success', 'Peticionario eliminado correctamente.');
    }

    public function toggleActive(Petitioner $petitioner)
    {
        $petitioner->update(['active' => !$petitioner->active]);

        return redirect()->route('petitioners.index')
            ->with('success', 'Estado del peticionario actualizado.');
    }
}