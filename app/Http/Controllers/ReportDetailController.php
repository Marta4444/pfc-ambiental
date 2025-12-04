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
     * PÚBLICO: Todos los usuarios autenticados pueden ver
     */
    public function index(Report $report)
    {
        $groupedDetails = $report->getGroupedDetails();
        
        // Obtener los campos de la subcategoría para mostrar labels correctos
        $subcategory = $report->subcategory;
        $fields = $subcategory->fields()->get()->keyBy('key_name');
        
        // Verificar si el usuario puede editar (para mostrar/ocultar botones)
        $canEdit = $this->canEdit($report);
        
        return view('report-details.index', compact('report', 'groupedDetails', 'fields', 'canEdit'));
    }

    /**
     * Formulario para crear nuevos detalles
     * RESTRINGIDO: Solo admin o asignado
     */
    public function create(Report $report)
    {
        $this->authorizeEdit($report);

        // Obtener la subcategoría del report
        $subcategory = $report->subcategory;
        
        // Obtener SOLO los campos asignados a esta subcategoría
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
     * RESTRINGIDO: Solo admin o asignado
     */
    public function store(Request $request, Report $report)
    {
        $this->authorizeEdit($report);

        $subcategory = $report->subcategory;
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
            $validFieldKeys = $fields->pluck('key_name')->toArray();

            // Primero buscar la especie si existe el campo
            if (!empty($fieldValues['especie'])) {
                $species = Species::where('scientific_name', $fieldValues['especie'])
                    ->orWhere('common_name', $fieldValues['especie'])
                    ->first();
                $speciesId = $species?->id;
            }

            foreach ($fieldValues as $fieldKey => $value) {
                if (!in_array($fieldKey, $validFieldKeys)) {
                    continue;
                }
                
                if (empty($value) && $value !== '0') {
                    continue;
                }

                $field = $fields->firstWhere('key_name', $fieldKey);
                if (!$field) continue;

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

            // Actualizar datos de protección en la tabla Species cuando el usuario los introduce manualmente si no existían antes.
            if ($speciesId) {
                $this->updateSpeciesProtectionData($speciesId, $fieldValues);
            }

            DB::commit();

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
     * PÚBLICO: Todos los usuarios autenticados pueden ver
     */
    public function show(Report $report, string $groupKey)
    {
        $details = ReportDetail::where('report_id', $report->id)
            ->where('group_key', $groupKey)
            ->orderBy('order_index')
            ->get();

        if ($details->isEmpty()) {
            return redirect()->route('report-details.index', $report)
                ->with('error', 'Grupo de detalles no encontrado.');
        }

        $subcategory = $report->subcategory;
        $fields = $subcategory->fields()->get()->keyBy('key_name');
        
        // Verificar si el usuario puede editar (para mostrar/ocultar botones)
        $canEdit = $this->canEdit($report);

        return view('report-details.show', compact('report', 'groupKey', 'details', 'fields', 'canEdit'));
    }

    /**
     * Formulario para editar un grupo de detalles
     * RESTRINGIDO: Solo admin o asignado
     */
    public function edit(Report $report, string $groupKey)
    {
        $this->authorizeEdit($report);

        $details = ReportDetail::where('report_id', $report->id)
            ->where('group_key', $groupKey)
            ->orderBy('order_index')
            ->get();

        if ($details->isEmpty()) {
            return redirect()->route('report-details.index', $report)
                ->with('error', 'Grupo de detalles no encontrado.');
        }

        $subcategory = $report->subcategory;
        $fields = $subcategory->fields()
            ->orderBy('subcategory_fields.order_index')
            ->get();

        $existingValues = $details->pluck('value', 'field_key')->toArray();
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
     * RESTRINGIDO: Solo admin o asignado
     */
    public function update(Request $request, Report $report, string $groupKey)
    {
        $this->authorizeEdit($report);

        $subcategory = $report->subcategory;
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
            $validFieldKeys = $fields->pluck('key_name')->toArray();

            // Primero buscar la especie
            if (!empty($fieldValues['especie'])) {
                $species = Species::where('scientific_name', $fieldValues['especie'])
                    ->orWhere('common_name', $fieldValues['especie'])
                    ->first();
                $speciesId = $species?->id;
            }

            foreach ($fieldValues as $fieldKey => $value) {
                if (!in_array($fieldKey, $validFieldKeys)) {
                    continue;
                }
                
                if (empty($value) && $value !== '0') {
                    continue;
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

            // Actualizar datos de protección en la tabla Species
            if ($speciesId) {
                $this->updateSpeciesProtectionData($speciesId, $fieldValues);
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
     * RESTRINGIDO: Solo admin o asignado
     */
    public function destroy(Report $report, string $groupKey)
    {
        $this->authorizeEdit($report);

        ReportDetail::where('report_id', $report->id)
            ->where('group_key', $groupKey)
            ->delete();

        return redirect()->route('report-details.index', $report)
            ->with('success', 'Grupo de detalles eliminado.');
    }

    /**
     * Actualizar datos de protección en la tabla Species
     * Solo actualiza campos que están vacíos en la tabla Species
     */
    protected function updateSpeciesProtectionData(int $speciesId, array $fieldValues): void
    {
        $species = Species::find($speciesId);
        if (!$species) return;

        $updated = false;

        // Mapeo de campos del formulario a campos de la tabla Species
        $protectionFieldsMap = [
            'boe_status' => 'boe_status',
            'ccaa_status' => 'ccaa_status',
            'iucn_category' => 'iucn_category',
        ];

        foreach ($protectionFieldsMap as $formField => $dbField) {
            $newValue = $fieldValues[$formField] ?? null;
            
            // Solo actualizar si:
            // 1. El campo en Species está vacío
            // 2. El nuevo valor no está vacío
            if (empty($species->{$dbField}) && !empty($newValue)) {
                $species->{$dbField} = $newValue;
                $updated = true;
            }
        }

        if ($updated) {
            // Recalcular is_protected basándose en los datos actualizados
            $species->is_protected = $this->calculateIsProtected($species);
            $species->save();
        }
    }

    /**
     * Calcular si una especie está protegida
     */
    protected function calculateIsProtected(Species $species): bool
    {
        // Está protegida si tiene estado BOE
        if (!empty($species->boe_status)) {
            return true;
        }

        // O si tiene CITES
        if (!empty($species->cites_appendix)) {
            return true;
        }

        // O si tiene categoría IUCN de riesgo
        $riskCategories = ['CR', 'EN', 'VU', 'NT'];
        if (!empty($species->iucn_category) && in_array($species->iucn_category, $riskCategories)) {
            return true;
        }

        return false;
    }

    /**
     * Verificar si el usuario puede editar el report
     * Solo admin o el usuario asignado pueden editar
     */
    protected function canEdit(Report $report): bool
    {
        $user = Auth::user();
        return $user->role === 'admin' || $report->assigned_to === $user->id;
    }

    /**
     * Autorizar edición del report
     * Lanza 403 si el usuario no puede editar
     */
    protected function authorizeEdit(Report $report): void
    {
        if (!$this->canEdit($report)) {
            abort(403, 'No tienes permiso para editar los detalles de este caso. Solo el agente asignado o un administrador pueden hacerlo.');
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