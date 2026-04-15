<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\User;
use App\Models\Petitioner;
use App\Models\ProtectedArea;
use App\Http\Controllers\Controller;
use App\Helpers\AuditHelper;
use App\Helpers\SpainGeoHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Mostrar la lista de casos.
     */
    public function index(Request $request)
    {
        $query = Report::with(['user', 'category', 'subcategory', 'petitioner', 'assignedTo']);

        // Filtro por búsqueda general
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ip', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('locality', 'like', "%{$search}%")
                  ->orWhere('background', 'like', "%{$search}%");
            });
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por categoría
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filtro por subcategoría
        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        // Filtro por urgencia
        if ($request->filled('urgency')) {
            $query->where('urgency', $request->urgency);
        }

        // Filtro por autor
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtro por agente asignado
        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        // Filtro por peticionario
        if ($request->filled('petitioner_id')) {
            $query->where('petitioner_id', $request->petitioner_id);
        }

        // Filtro por comunidad
        if ($request->filled('community')) {
            $query->where('community', $request->community);
        }

        // Filtro por provincia
        if ($request->filled('province')) {
            $query->where('province', $request->province);
        }

        // Filtro por rango de fechas
        if ($request->filled('date_from')) {
            $query->where('date_petition', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date_petition', '<=', $request->date_to);
        }

        // Ordenar por fecha de creación (más reciente primero)
        $reports = $query->orderBy('created_at', 'desc')->paginate(15);

        // Datos para filtros
        $categories = Category::where('active', true)->orderBy('name')->get();
        $subcategories = Subcategory::where('active', true)->orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $petitioners = Petitioner::where('active', true)->orderBy('order')->get();
        $statuses = Report::STATUS_LABELS;
        $urgencies = Report::URGENCY_LABELS;
        
        $communities = Report::distinct()->pluck('community')->filter()->sort()->values();
        $provinces = Report::distinct()->pluck('province')->filter()->sort()->values();

        return view('reports.index', compact(
            'reports', 
            'categories', 
            'subcategories',
            'users',
            'petitioners',
            'statuses', 
            'urgencies',
            'communities',
            'provinces'
        ));
    }

    /**
     * Mostrar el formulario para crear un nuevo caso.
     */
    public function create()
    {
        $categories = Category::where('active', true)->get();
        $subcategories = Subcategory::where('active', true)->get();
        $agents = User::where('role', 'user')->get();
        $petitioners = Petitioner::where('active', true)->orderBy('order')->get();
        
        return view('reports.create', compact('categories', 'subcategories', 'agents', 'petitioners'));
    }

    /**
     * Guardar un nuevo caso.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ip' => [
                'required',
                'string',
                'max:20',
                'regex:/^\d{4}-IP\d+$/',
                'unique:reports,ip'
            ],
            'title' => 'required|string|max:255',
            'background' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'community' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'locality' => 'required|string|max:255',
            'coordinates' => 'nullable|string|max:100',
            'petitioner_id' => 'required|exists:petitioners,id',
            'petitioner_other' => 'nullable|string|max:255',
            'office' => 'nullable|string|max:255',
            'diligency' => 'nullable|string|max:100',
            'urgency' => ['required', \Illuminate\Validation\Rule::in(Report::VALID_URGENCIES)],
            'date_petition' => 'required|date',
            'date_damage' => 'required|date',
            'assigned_to' => 'nullable|exists:users,id',
            'pdf_report' => 'nullable|file|mimes:pdf|max:20480',
            'vr_total' => 'nullable|numeric|min:0',
            've_total' => 'nullable|numeric|min:0',
            'vs_total' => 'nullable|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
        ], [
            'ip.required' => 'El número IP es obligatorio.',
            'ip.regex' => 'El formato del IP debe ser: AAAA-IPNNN (ejemplo: 2025-IP312)',
            'ip.unique' => 'Este número IP ya está registrado en otro informe.',
            'background.required' => 'Los antecedentes son obligatorios.',
            'petitioner_id.required' => 'Debe seleccionar una unidad peticionaria.',
        ]);

        $petitioner = Petitioner::find($validated['petitioner_id']);
        if ($petitioner && $petitioner->name === 'Otro' && empty($validated['petitioner_other'])) {
            return back()
                ->withErrors(['petitioner_other' => 'Debe especificar la unidad peticionaria cuando selecciona "Otro".'])
                ->withInput();
        }

        // Validación geográfica
        $geoValidation = SpainGeoHelper::validateGeoData(
            $validated['community'],
            $validated['province'],
            $validated['coordinates'] ?? null
        );
        
        if (!$geoValidation['valid']) {
            return back()
                ->withErrors($geoValidation['errors'])
                ->withInput();
        }

        $belongs = Subcategory::where('id', $validated['subcategory_id'])
            ->where('category_id', $validated['category_id'])
            ->exists();

        if (!$belongs) {
            return back()
                ->withErrors(['subcategory_id' => 'La subcategoría no pertenece a la categoría seleccionada.'])
                ->withInput();
        }

        $path = $this->storePdfAttachment($request);

        $assigned = !empty($validated['assigned_to']);
        
        // Determinar estado inicial: NUEVO si no hay asignado, EN_ESPERA si está asignado
        $initialStatus = $assigned ? Report::STATUS_EN_ESPERA : Report::STATUS_NUEVO;

        $report = Report::create([
            'user_id' => Auth::id(),
            'category_id' => $validated['category_id'],
            'subcategory_id' => $validated['subcategory_id'],
            'ip' => $validated['ip'],
            'title' => $validated['title'],
            'background' => $validated['background'],
            'community' => $validated['community'],
            'province' => $validated['province'],
            'locality' => $validated['locality'],
            'coordinates' => $validated['coordinates'] ?? null,
            'petitioner_id' => $validated['petitioner_id'],
            'petitioner_other' => $petitioner->name === 'Otro' ? $validated['petitioner_other'] : null,
            'office' => $validated['office'] ?? null,
            'diligency' => $validated['diligency'] ?? null,
            'urgency' => $validated['urgency'],
            'date_petition' => $validated['date_petition'],
            'date_damage' => $validated['date_damage'],
            'status' => $initialStatus,
            'assigned' => $assigned,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'pdf_report' => $path,
            'vr_total' => $validated['vr_total'] ?? null,
            've_total' => $validated['ve_total'] ?? null,
            'vs_total' => $validated['vs_total'] ?? null,
            'total_cost' => $validated['total_cost'] ?? null,
        ]);

        return redirect()->route('reports.show', $report)->with('success', 'Caso creado correctamente.');
    }

    /**
     * Mostrar un caso específico.
     */
    public function show(Report $report)
    {
        $report->load(['user', 'category', 'subcategory', 'petitioner', 'assignedTo']);
        
        // Obtener lista de agentes para asignación
        $agents = User::where('role', 'user')->orderBy('name')->get();
        
        // Verificar si las coordenadas están en área protegida
        $protectedAreas = collect();
        if ($report->coordinates) {
            $coords = SpainGeoHelper::parseCoordinates($report->coordinates);
            if ($coords) {
                $protectedAreas = ProtectedArea::findAreasContainingPoint($coords['lat'], $coords['lng']);
            }
        }
        
        return view('reports.show', compact('report', 'agents', 'protectedAreas'));
    }
    
    /**
     * Mostrar el formulario para editar un caso.
     */
    public function edit(Report $report)
    {
        // Bloquear edición si el caso está finalizado
        if ($report->isFinalizado()) {
            return redirect()->route('reports.show', $report)
                ->with('error', 'Este caso está finalizado y no se puede editar. Contacte con un administrador para reabrirlo.');
        }

        // CAMBIO: Permitir edición al usuario asignado o un admin
        $user = Auth::user();
        $canEdit = $user->role === 'admin' || 
                   $report->assigned_to === $user->id;

        if (!$canEdit) {
            abort(403, 'No tienes permiso para editar este caso.');
        }

        $categories = Category::where('active', true)->get();
        $subcategories = Subcategory::where('active', true)->get();
        $agents = User::where('role', 'user')->get();
        $petitioners = Petitioner::where('active', true)->orderBy('order')->get();
        
        return view('reports.edit', compact('report', 'categories', 'subcategories', 'agents', 'petitioners'));
    }

    /**
     * Actualizar un caso.
     */
    public function update(Request $request, Report $report)
{
    // Bloquear actualización si el caso está finalizado
    if ($report->isFinalizado()) {
        return redirect()->route('reports.show', $report)
            ->with('error', 'Este caso está finalizado y no se puede editar. Contacte con un administrador para reabrirlo.');
    }

    $user = Auth::user();
    $isAdmin = $user->role === 'admin';
    $isOwner = $user->id === $report->user_id;
    $isAssigned = $user->id === $report->assigned_to;

    if (!$isAdmin && !$isOwner && !$isAssigned) {
        abort(403, 'No tienes permiso para actualizar este caso.');
    }

    // CAMBIO: Validación diferencial según rol
    if ($isAdmin) {
        // Admin puede editar TODO
        $validated = $request->validate([
            'ip' => [
                'required',
                'string',
                'max:20',
                'regex:/^\d{4}-IP\d+$/',
                'unique:reports,ip,' . $report->id
            ],
            'title' => 'required|string|max:255',
            'background' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'community' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'locality' => 'required|string|max:255',
            'coordinates' => 'nullable|string|max:100',
            'petitioner_id' => 'required|exists:petitioners,id',
            'petitioner_other' => 'nullable|string|max:255',
            'office' => 'nullable|string|max:255',
            'diligency' => 'nullable|string|max:100',
            'urgency' => ['required', \Illuminate\Validation\Rule::in(Report::VALID_URGENCIES)],
            'date_petition' => 'required|date',
            'date_damage' => 'required|date',
            'status' => ['required', \Illuminate\Validation\Rule::in(Report::VALID_STATUSES)],
            'assigned_to' => 'nullable|exists:users,id',
            'pdf_report' => 'nullable|file|mimes:pdf|max:20480',
            'vr_total' => 'nullable|numeric|min:0',
            've_total' => 'nullable|numeric|min:0',
            'vs_total' => 'nullable|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
        ]);

        $petitioner = Petitioner::find($validated['petitioner_id']);
        if ($petitioner && $petitioner->name === 'Otro' && empty($validated['petitioner_other'])) {
            return back()
                ->withErrors(['petitioner_other' => 'Debe especificar la unidad peticionaria.'])
                ->withInput();
        }

        $belongs = Subcategory::where('id', $validated['subcategory_id'])
            ->where('category_id', $validated['category_id'])
            ->exists();

        if (!$belongs) {
            return back()
                ->withErrors(['subcategory_id' => 'La subcategoría no pertenece a la categoría.'])
                ->withInput();
        }

        // Validación geográfica (admin)
        $geoValidation = SpainGeoHelper::validateGeoData(
            $validated['community'],
            $validated['province'],
            $validated['coordinates'] ?? null
        );
        
        if (!$geoValidation['valid']) {
            return back()
                ->withErrors($geoValidation['errors'])
                ->withInput();
        }

        $newPath = $this->storePdfAttachment($request, $report->pdf_report);
        if ($newPath) {
            $validated['pdf_report'] = $newPath;
        }

        $validated['assigned'] = !empty($validated['assigned_to']);
        $validated['petitioner_other'] = $petitioner->name === 'Otro' ? $validated['petitioner_other'] : null;

    } else {
        // Usuario normal (creador o asignado) puede editar campos limitados
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'background' => 'required|string',
            'community' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'locality' => 'required|string|max:255',
            'coordinates' => 'nullable|string|max:100',
            'petitioner_id' => 'required|exists:petitioners,id',
            'petitioner_other' => 'nullable|string|max:255',
            'office' => 'nullable|string|max:255',
            'diligency' => 'nullable|string|max:100',
            'urgency' => ['required', \Illuminate\Validation\Rule::in(Report::VALID_URGENCIES)],
            'date_petition' => 'required|date',
            'date_damage' => 'required|date',
            'pdf_report' => 'nullable|file|mimes:pdf|max:20480',
            'vr_total' => 'nullable|numeric|min:0',
            've_total' => 'nullable|numeric|min:0',
            'vs_total' => 'nullable|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
        ]);

        $petitioner = Petitioner::find($validated['petitioner_id']);
        if ($petitioner && $petitioner->name === 'Otro' && empty($validated['petitioner_other'])) {
            return back()
                ->withErrors(['petitioner_other' => 'Debe especificar la unidad peticionaria.'])
                ->withInput();
        }

        // Validación geográfica (usuario normal)
        $geoValidation = SpainGeoHelper::validateGeoData(
            $validated['community'],
            $validated['province'],
            $validated['coordinates'] ?? null
        );
        
        if (!$geoValidation['valid']) {
            return back()
                ->withErrors($geoValidation['errors'])
                ->withInput();
        }

        $newPath = $this->storePdfAttachment($request, $report->pdf_report);
        if ($newPath) {
            $validated['pdf_report'] = $newPath;
        }

        // Mantener campos que no pueden editar
        $validated['petitioner_other'] = $petitioner && $petitioner->name === 'Otro'
            ? $validated['petitioner_other']
            : null;

        // El estado no es editable por el usuario normal
        unset($validated['status']);
    }

    $report->update($validated);

    return redirect()->route('reports.show', $report)->with('success', 'Caso actualizado correctamente.');
}

    /**
     * Eliminar un caso.
     */
    public function destroy(Report $report)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'No tienes permiso para eliminar casos.');
        }

        if ($report->pdf_report) {
            Storage::disk('public')->delete($report->pdf_report);
        }

        $report->delete();

        return redirect()->route('reports.index')->with('success', 'Caso eliminado correctamente.');
    }

    /**
     * Asignar un caso a un usuario.
     */
    public function assign(Request $request, Report $report)
    {
        // Cualquier usuario autenticado puede asignar casos
        // Admin tiene permiso total
        $user = Auth::user();
       
        $canAssign = $user->role === 'admin' || 
                     !$report->assigned || 
                     $report->user_id === $user->id || 
                     $report->assigned_to === $user->id;
        
        if (!$canAssign) {
            return redirect()->back()->with('error', 'No tienes permiso para asignar este caso. El caso ya está asignado a otro usuario.');
        }

        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $report->update([
            'assigned_to' => $request->assigned_to,
            'assigned' => true,
            'status' => $this->resolveStatusAfterAssignment($report, true),
        ]);

        $assignedUser = User::find($request->assigned_to);
        AuditHelper::logAssign($report, $assignedUser?->name);

        return redirect()->back()->with('success', 'Caso asignado correctamente.');
    }

    /**
     * Desasignar un caso de un usuario.
     */
    public function unassign(Report $report)
    {
        // Admin puede desasignar cualquier caso
        // Usuario puede desasignarse solo si está asignado a él mismo
        $user = Auth::user();
        
        $canUnassign = $user->role === 'admin' || $report->assigned_to === $user->id;
        
        if (!$canUnassign) {
            abort(403, 'No tienes permiso para desasignar este caso.');
        }

        $report->update([
            'assigned_to' => null,
            'assigned' => false,
            'status' => $this->resolveStatusAfterAssignment($report, false),
        ]);

        AuditHelper::logUnassign($report);

        return redirect()->back()->with('success', 'Caso desasignado correctamente.');
    }

    /**
     * Auto-asignar un caso al usuario autenticado.
     */
    public function selfAssign(Report $report)
    {
        $user = Auth::user();
        
        // Verificar que el caso no esté ya asignado
        if ($report->assigned) {
            return redirect()->back()->with('error', 'Este caso ya está asignado a otro usuario.');
        }

        $report->update([
            'assigned_to' => $user->id,
            'assigned' => true,
            'status' => $this->resolveStatusAfterAssignment($report, true),
        ]);

        AuditHelper::logSelfAssign($report);

        return redirect()->back()->with('success', 'Te has asignado el caso correctamente.');
    }

    /**
     * Finalizar un caso (cerrarlo permanentemente).
     */
    public function finalize(Report $report)
    {
        $user = Auth::user();
        
        // Verificar permisos: admin puede finalizar cualquier caso, usuario solo si está asignado a él
        $canFinalize = $user->role === 'admin' || $report->assigned_to === $user->id;
        
        if (!$canFinalize) {
            return redirect()->back()->with('error', 'No tienes permiso para finalizar este caso.');
        }

        // Verificar que el caso puede ser finalizado (tiene detalles y costes)
        if (!$report->canBeFinalized()) {
            return redirect()->back()->with('error', 'El caso no puede ser finalizado. Debe tener detalles y costes calculados.');
        }

        $report->update([
            'status' => Report::STATUS_COMPLETADO,
        ]);

        return redirect()->route('reports.show', $report)->with('success', 'El caso ha sido finalizado correctamente. Ya no se puede editar.');
    }

    /**
     * Reabrir un caso finalizado (solo admin).
     */
    public function reopen(Report $report)
    {
        $user = Auth::user();
        
        // Solo admin puede reabrir casos
        if ($user->role !== 'admin') {
            abort(403, 'Solo los administradores pueden reabrir casos finalizados.');
        }

        // Verificar que el caso está finalizado
        if (!$report->isFinalizado()) {
            return redirect()->back()->with('error', 'El caso no está finalizado.');
        }

        $report->update([
            'status' => Report::STATUS_EN_PROCESO,
        ]);

        return redirect()->route('reports.show', $report)->with('success', 'El caso ha sido reabierto y ahora puede ser editado.');
    }

    /**
     * Exportar un informe en PDF.
     */
    public function exportPdf(Report $report)
    {
        // Cargar relaciones necesarias
        $report->load(['user', 'category', 'subcategory', 'petitioner', 'assignedTo', 'costItems']);
        
        // Obtener detalles agrupados
        $groupedDetails = $report->details()
            ->with(['species', 'protectedArea'])
            ->orderBy('group_key')
            ->orderBy('order_index')
            ->get()
            ->groupBy('group_key');
        
        // Obtener items de coste
        $costItems = $report->costItems()
            ->orderBy('cost_type')
            ->orderBy('group_key')
            ->get();
        
        // Verificar áreas protegidas por coordenadas
        $protectedAreas = collect();
        if ($report->coordinates) {
            $coords = SpainGeoHelper::parseCoordinates($report->coordinates);
            if ($coords) {
                $protectedAreas = ProtectedArea::findAreasContainingPoint($coords['lat'], $coords['lng']);
            }
        }
        
        // Generar PDF
        $pdf = Pdf::loadView('reports.pdf', [
            'report' => $report,
            'groupedDetails' => $groupedDetails,
            'costItems' => $costItems,
            'protectedAreas' => $protectedAreas,
        ]);
        
        // Configurar opciones del PDF
        $pdf->setPaper('A4', 'portrait');
        
        // Nombre del archivo
        $filename = 'caso_' . $report->ip . '_' . now()->format('Ymd_His') . '.pdf';

        AuditHelper::logExport('PDF', ['report_id' => $report->id, 'report_ip' => $report->ip]);

        // Descargar el PDF
        return $pdf->download($filename);
    }

    /**
     * Descargar el PDF adjunto del informe.
     */
    public function downloadAttachment(Report $report)
    {
        if (!$report->pdf_report || !Storage::disk('public')->exists($report->pdf_report)) {
            abort(404, 'El archivo no existe.');
        }

        $filename = basename($report->pdf_report);
        return Storage::disk('public')->download($report->pdf_report, $filename);
    }

    /**
     * Sube y almacena el PDF adjunto, eliminando el anterior si existe.
     */
    private function storePdfAttachment(Request $request, ?string $oldPath = null): ?string
    {
        if (!$request->hasFile('pdf_report')) {
            return null;
        }
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }
        $originalName = $request->file('pdf_report')->getClientOriginalName();
        $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        return $request->file('pdf_report')->storeAs('reports', time() . '_' . $sanitizedName, 'public');
    }

    /**
     * Calcula el estado del informe tras una operación de asignación o desasignación.
     */
    private function resolveStatusAfterAssignment(Report $report, bool $isAssigning): string
    {
        $hasDetails = $report->details()->exists();

        if ($isAssigning) {
            $newStatus = $hasDetails ? Report::STATUS_EN_PROCESO : Report::STATUS_EN_ESPERA;
            $shouldChange = in_array($report->status, [Report::STATUS_NUEVO, Report::STATUS_EN_ESPERA]);
        } else {
            $newStatus = $hasDetails ? Report::STATUS_EN_PROCESO : Report::STATUS_NUEVO;
            $shouldChange = $report->status !== Report::STATUS_COMPLETADO;
        }

        return $shouldChange ? $newStatus : $report->status;
    }
}