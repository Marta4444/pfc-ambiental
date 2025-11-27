<?php

namespace App\Http\Controllers;

use App\Models\Field;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FieldController extends Controller
{
       /**
     * Mostrar la listsa de campos.
     */
    public function index()
    {
        $fields = Field::with('subcategories')
            ->orderBy('active', 'desc')
            ->orderBy('label', 'asc')
            ->paginate(20);

        return view('fields.index', compact('fields'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $types = Field::VALID_TYPES;
        return view('fields.create', compact('types'));
    }

    /**
     *Guardar un nuevo campo.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key_name' => 'required|string|max:100|unique:fields,key_name|regex:/^[a-z0-9_]+$/',
            'label' => 'required|string|max:255',
            'type' => ['required', Rule::in(Field::VALID_TYPES)],
            'units' => 'nullable|string|max:50',
            'options_json' => 'nullable|json',
            'help_text' => 'nullable|string',
            'placeholder' => 'nullable|string|max:255',
            'is_numeric' => 'boolean',
            'active' => 'boolean',
        ], [
            'key_name.regex' => 'El nombre clave solo puede contener letras minúsculas, números y guiones bajos.',
            'key_name.unique' => 'Ya existe un campo con este nombre clave.',
            'options_json.json' => 'Las opciones deben ser un JSON válido.',
        ]);

        // Convertir options_json de string a array si existe
        if (!empty($validated['options_json'])) {
            $validated['options_json'] = json_decode($validated['options_json'], true);
        }

        $field = Field::create($validated);

        return redirect()->route('fields.show', $field)
            ->with('success', 'Campo creado correctamente.');
    }

    /**
     * Mostrar un campo.
     */
    public function show(Field $field)
    {
        $field->load('subcategories');
        $availableSubcategories = Subcategory::where('active', true)
            ->whereNotIn('id', $field->subcategories->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('fields.show', compact('field', 'availableSubcategories'));
    }

    /**
     * Mostrar el formulario para editar un campo.
     */
    public function edit(Field $field)
    {
        $types = Field::VALID_TYPES;
        return view('fields.edit', compact('field', 'types'));
    }

    /**
     * Actualizar un campo en Fields
     */
    public function update(Request $request, Field $field)
    {
        $validated = $request->validate([
            'key_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('fields', 'key_name')->ignore($field->id)
            ],
            'label' => 'required|string|max:255',
            'type' => ['required', Rule::in(Field::VALID_TYPES)],
            'units' => 'nullable|string|max:50',
            'options_json' => 'nullable|json',
            'help_text' => 'nullable|string',
            'placeholder' => 'nullable|string|max:255',
            'is_numeric' => 'boolean',
            'active' => 'boolean',
        ]);

        // Convertir options_json de string a array si existe
        if (!empty($validated['options_json'])) {
            $validated['options_json'] = json_decode($validated['options_json'], true);
        }

        $field->update($validated);

        return redirect()->route('fields.show', $field)
            ->with('success', 'Campo actualizado correctamente.');
    }

    /**
     * Eliminar un campo
     */
    public function destroy(Field $field)
    {
        // Verificar si está asignado a alguna subcategoría
        if ($field->subcategories()->exists()) {
            return redirect()->route('fields.index')
                ->with('error', 'No se puede eliminar el campo porque está asignado a ' . $field->subcategories()->count() . ' subcategoría(s).');
        }

        $field->delete();

        return redirect()->route('fields.index')
            ->with('success', 'Campo eliminado correctamente.');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(Field $field)
    {
        $field->update(['active' => !$field->active]);

        $status = $field->active ? 'activado' : 'desactivado';
        return redirect()->back()
            ->with('success', "Campo {$status} correctamente.");
    }

    /**
     * Añadir un campo a una subcategoría
     */
    public function assignToSubcategory(Request $request, Field $field)
    {
        $validated = $request->validate([
            'subcategory_id' => 'required|exists:subcategories,id',
            'is_required' => 'boolean',
            'order_index' => 'nullable|integer|min:0',
            'default_value' => 'nullable|string|max:255',
        ]);

        // Verificar si ya está asignado
        if ($field->subcategories()->where('subcategory_id', $validated['subcategory_id'])->exists()) {
            return redirect()->back()
                ->with('error', 'Este campo ya está asignado a la subcategoría seleccionada.');
        }

        // Obtener el siguiente order_index si no se proporciona
        if (!isset($validated['order_index'])) {
            $maxOrder = \DB::table('subcategory_fields')
                ->where('subcategory_id', $validated['subcategory_id'])
                ->max('order_index');
            $validated['order_index'] = ($maxOrder ?? 0) + 1;
        }

        $field->subcategories()->attach($validated['subcategory_id'], [
            'is_required' => $validated['is_required'] ?? false,
            'order_index' => $validated['order_index'],
            'default_value' => $validated['default_value'] ?? null,
        ]);

        return redirect()->back()
            ->with('success', 'Campo asignado correctamente a la subcategoría.');
    }

    /**
     * Desasignar un campo de una subcategoría
     */
    public function unassignFromSubcategory(Field $field, Subcategory $subcategory)
    {
        $field->subcategories()->detach($subcategory->id);

        return redirect()->back()
            ->with('success', 'Campo desasignado correctamente de la subcategoría.');
    }

    /**
     * Actalizar datos en la tabla pivot de subcategory_fields
     */
    public function updateSubcategoryPivot(Request $request, Field $field, Subcategory $subcategory)
    {
        $validated = $request->validate([
            'is_required' => 'boolean',
            'order_index' => 'required|integer|min:0',
            'default_value' => 'nullable|string|max:255',
        ]);

        $field->subcategories()->updateExistingPivot($subcategory->id, $validated);

        return redirect()->back()
            ->with('success', 'Configuración actualizada correctamente.');
    }
}
