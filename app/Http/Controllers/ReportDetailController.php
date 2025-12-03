<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportDetail;
use App\Models\Subcategory;
use App\Models\Field;
use App\Models\Species;
use App\Models\ProtectedArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportDetailController extends Controller
{
    /**
     * Mostrar todos los detalles de un report
     */
    public function index(Report $report)
    {
        $this->authorizeAccess($report);

        $groupedDetails = $report->getGroupedDetails();
        
        // Obtener los campos de la subcategoría para mostrar labels correctos
        $subcategory = $report->subcategory;
        $fields = $subcategory->fields()->get()->keyBy('key_name');
        
        return view('report-details.index', compact('report', 'groupedDetails', 'fields'));
    }

    /**
     * Formulario para crear nuevos detalles
     */
    public function create(Report $report)
    {
        $this->authorizeAccess($report);

        // Obtener la subcategoría del report
        $subcategory = $report->subcategory;
        
        // ✅ CORREGIDO: Obtener SOLO los campos asignados a esta subcategoría
        // La relación fields() ya filtra por subcategory_id a través de la tabla pivot
        $fields = $subcategory->fields()
            ->orderBy('subcategory_fields.order_index')
            ->get();

        // Si no hay campos configurados, mostrar mensaje
        if ($fields->isEmpty()) {
            return redirect()->route('reports.show', $report)
                ->with('error', 'No hay campos configurados para esta subcategoría. Contacta con un administrador.');
        }

        // Generar el próximo group_key
        $groupPrefix = $this->getGroupPrefixForSubcategory($subcategory);
        $nextGroupKey = ReportDetail::generateNextGroupKey($report->id, $groupPrefix);

        // Obtener especies para autocompletado (si hay campo de especie)
        $hasSpeciesField = $fields->contains('key_name', 'especie');
        
        return view('report-details.create', compact(
            'report', 
            'subcategory', 
            'fields', 
            'nextGroupKey',
            'hasSpeciesField'
        ));
    }

    /**
     * Guardar nuevos detalles
     */
    public function store(Request $request, Report $report)
    {
        $this->authorizeAccess($report);

        $subcategory = $report->subcategory;
        
        // ✅ CORREGIDO: Obtener solo los campos de la subcategoría
        $fields = $subcategory->fields()->get();

        // Validar campos requeridos
        $rules = [];
        $messages = [];

        foreach ($fields as $field) {
            $pivot = $field->pivot;
            $fieldKey = "fields.{$field->key_name}";
            
            $fieldRules = [];
            
            if ($pivot->is_required) {
                $fieldRules[] = 'required';
                $messages["{$fieldKey}.required"] = "El campo '{$field->label}' es obligatorio.";
            } else {
                $fieldRules[] = 'nullable';
            }

            // Añadir reglas según tipo
            if ($field->is_numeric) {
                $fieldRules[] = 'numeric';
            }

            if (!empty($fieldRules)) {
                $rules[$fieldKey] = implode('|', $fieldRules);
            }
        }

        $rules['group_key'] = 'required|string|max:50';
        $validated = $request->validate($rules, $messages);

        $groupKey = $validated['group_key'];
        $fieldValues = $request->input('fields', []);

        DB::beginTransaction();

        try {
            $orderIndex = 0;
            $speciesId = null;
            $protectedAreaId = null;

            // ✅ Solo procesar campos que pertenecen a la subcategoría
            $validFieldKeys = $fields->pluck('key_name')->toArray();

            foreach ($fieldValues as $fieldKey => $value) {
                // Ignorar campos que no pertenecen a esta subcategoría
                if (!in_array($fieldKey, $validFieldKeys)) {
                    continue;
                }
                
                if (empty($value) && $value !== '0') {
                    continue; // Saltar campos vacíos
                }

                $field = $fields->firstWhere('key_name', $fieldKey);
                if (!$field) continue;

                // Si es campo de especie, buscar/crear referencia
                if ($fieldKey === 'especie' && !empty($value)) {
                    $species = Species::where('scientific_name', $value)
                        ->orWhere('common_name', $value)
                        ->first();
                    $speciesId = $species?->id;
                }

                ReportDetail::create([
                    'report_id' => $report->id,
                    'group_key' => $groupKey,
                    'field_key' => $fieldKey,
                    'value' => $value,
                    'species_id' => $speciesId,
                    'protected_area_id' => $protectedAreaId,
                    'order_index' => $orderIndex++,
                ]);
            }

            DB::commit();

            // Verificar si quiere añadir más
            if ($request->has('add_another')) {
                return redirect()->route('report-details.create', $report)
                    ->with('success', 'Detalles guardados. Puedes añadir más.');
            }

            return redirect()->route('report-details.index', $report)
                ->with('success', 'Detalles del caso guardados correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Error al guardar los detalles: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar un grupo específico de detalles
     */
    public function show(Report $report, string $groupKey)
    {
        $this->authorizeAccess($report);

        $details = ReportDetail::where('report_id', $report->id)
            ->where('group_key', $groupKey)
            ->orderBy('order_index')
            ->get();

        if ($details->isEmpty()) {
            return redirect()->route('report-details.index', $report)
                ->with('error', 'Grupo de detalles no encontrado.');
        }

        // ✅ Obtener los campos de la subcategoría para mostrar labels
        $subcategory = $report->subcategory;
        $fields = $subcategory->fields()->get()->keyBy('key_name');

        return view('report-details.show', compact('report', 'groupKey', 'details', 'fields'));
    }

    /**
     * Formulario para editar un grupo de detalles
     */
    public function edit(Report $report, string $groupKey)
    {
        $this->authorizeAccess($report);

        $details = ReportDetail::where('report_id', $report->id)
            ->where('group_key', $groupKey)
            ->orderBy('order_index')
            ->get();

        if ($details->isEmpty()) {
            return redirect()->route('report-details.index', $report)
                ->with('error', 'Grupo de detalles no encontrado.');
        }

        $subcategory = $report->subcategory;
        
        // ✅ CORREGIDO: Obtener solo los campos de la subcategoría
        $fields = $subcategory->fields()
            ->orderBy('subcategory_fields.order_index')
            ->get();

        // Crear mapa de valores existentes
        $existingValues = $details->pluck('value', 'field_key')->toArray();
        
        // Verificar si hay campo de especie
        $hasSpeciesField = $fields->contains('key_name', 'especie');

        return view('report-details.edit', compact(
            'report', 
            'groupKey', 
            'details', 
            'subcategory', 
            'fields', 
            'existingValues',
            'hasSpeciesField'
        ));
    }

    /**
     * Actualizar un grupo de detalles
     */
    public function update(Request $request, Report $report, string $groupKey)
    {
        $this->authorizeAccess($report);

        $subcategory = $report->subcategory;
        
        // ✅ CORREGIDO: Obtener solo los campos de la subcategoría
        $fields = $subcategory->fields()->get();
        $fieldValues = $request->input('fields', []);

        // Validar campos requeridos
        $rules = [];
        $messages = [];

        foreach ($fields as $field) {
            $pivot = $field->pivot;
            $fieldKey = "fields.{$field->key_name}";
            
            $fieldRules = [];
            
            if ($pivot->is_required) {
                $fieldRules[] = 'required';
                $messages["{$fieldKey}.required"] = "El campo '{$field->label}' es obligatorio.";
            } else {
                $fieldRules[] = 'nullable';
            }

            if ($field->is_numeric) {
                $fieldRules[] = 'numeric';
            }

            if (!empty($fieldRules)) {
                $rules[$fieldKey] = implode('|', $fieldRules);
            }
        }

        $request->validate($rules, $messages);

        DB::beginTransaction();

        try {
            // Eliminar detalles existentes del grupo
            ReportDetail::where('report_id', $report->id)
                ->where('group_key', $groupKey)
                ->delete();

            // Recrear con nuevos valores
            $orderIndex = 0;
            $speciesId = null;
            $protectedAreaId = null;
            
            // ✅ Solo procesar campos que pertenecen a la subcategoría
            $validFieldKeys = $fields->pluck('key_name')->toArray();

            foreach ($fieldValues as $fieldKey => $value) {
                // Ignorar campos que no pertenecen a esta subcategoría
                if (!in_array($fieldKey, $validFieldKeys)) {
                    continue;
                }
                
                if (empty($value) && $value !== '0') {
                    continue;
                }

                if ($fieldKey === 'especie' && !empty($value)) {
                    $species = Species::where('scientific_name', $value)
                        ->orWhere('common_name', $value)
                        ->first();
                    $speciesId = $species?->id;
                }

                ReportDetail::create([
                    'report_id' => $report->id,
                    'group_key' => $groupKey,
                    'field_key' => $fieldKey,
                    'value' => $value,
                    'species_id' => $speciesId,
                    'protected_area_id' => $protectedAreaId,
                    'order_index' => $orderIndex++,
                ]);
            }

            DB::commit();

            return redirect()->route('report-details.index', $report)
                ->with('success', 'Detalles actualizados correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un grupo de detalles
     */
    public function destroy(Report $report, string $groupKey)
    {
        $this->authorizeAccess($report);

        ReportDetail::where('report_id', $report->id)
            ->where('group_key', $groupKey)
            ->delete();

        return redirect()->route('report-details.index', $report)
            ->with('success', 'Grupo de detalles eliminado.');
    }

    /**
     * Verificar acceso al report
     */
    protected function authorizeAccess(Report $report): void
    {
        $user = Auth::user();
        $canAccess = $user->role === 'admin' 
            || $report->user_id === $user->id 
            || $report->assigned_to === $user->id;

        if (!$canAccess) {
            abort(403, 'No tienes permiso para acceder a los detalles de este caso.');
        }
    }

    /**
     * Obtener prefijo de grupo según la subcategoría
     */
    protected function getGroupPrefixForSubcategory(Subcategory $subcategory): string
    {
        $name = strtolower($subcategory->name);

        if (str_contains($name, 'caza') || str_contains($name, 'especie') || str_contains($name, 'comercio')) {
            return 'species';
        }
        if (str_contains($name, 'vertido') || str_contains($name, 'residuo')) {
            return 'residue';
        }
        if (str_contains($name, 'emisión') || str_contains($name, 'atmosférica')) {
            return 'emission';
        }
        if (str_contains($name, 'agua') || str_contains($name, 'extracción')) {
            return 'water';
        }

        return 'group';
    }
}