<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\User;
use App\Models\Petitioner;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
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
     * Show the form for creating a new resource.
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
     * Store a newly created resource in storage.
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

        $belongs = Subcategory::where('id', $validated['subcategory_id'])
            ->where('category_id', $validated['category_id'])
            ->exists();

        if (!$belongs) {
            return back()
                ->withErrors(['subcategory_id' => 'La subcategoría no pertenece a la categoría seleccionada.'])
                ->withInput();
        }

        $path = null;
        if ($request->hasFile('pdf_report')) {
            $originalName = $request->file('pdf_report')->getClientOriginalName();
            $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
            $path = $request->file('pdf_report')->storeAs('reports', time() . '_' . $sanitizedName, 'public');
        }

        $assigned = !empty($validated['assigned_to']);

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
            'status' => Report::STATUS_NUEVO,
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
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        $report->load(['user', 'category', 'subcategory', 'petitioner', 'assignedTo']);
        
        // Obtener lista de agentes para asignación
        $agents = User::where('role', 'user')->orderBy('name')->get();
        
        return view('reports.show', compact('report', 'agents'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        // Bloquear edición si el caso está finalizado
        if ($report->isFinalizado()) {
            return redirect()->route('reports.show', $report)
                ->with('error', 'Este caso está finalizado y no se puede editar. Contacte con un administrador para reabrirlo.');
        }

        // CAMBIO: Permitir edición a creador, asignado o admin
        $user = Auth::user();
        $canEdit = $user->role === 'admin' || 
                   $report->user_id === $user->id || 
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
     * Update the specified resource in storage.
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

        if ($request->hasFile('pdf_report')) {
            if ($report->pdf_report) {
                Storage::disk('public')->delete($report->pdf_report);
            }
            
            $originalName = $request->file('pdf_report')->getClientOriginalName();
            $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
            $validated['pdf_report'] = $request->file('pdf_report')->storeAs('reports', time() . '_' . $sanitizedName, 'public');
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
            'petitioner_other' => 'nullable|string|max:255',
            'office' => 'nullable|string|max:255',
'diligency' => 'nullable|string|max:100',
            'urgency' => ['required', \Illuminate\Validation\Rule::in(Report::VALID_URGENCIES)],
            'date_petition' => 'required|date',
            'date_damage' => 'required|date',
            'status' => ['required', \Illuminate\Validation\Rule::in(Report::VALID_STATUSES)],
            'pdf_report' => 'nullable|file|mimes:pdf|max:20480',
            'vr_total' => 'nullable|numeric|min:0',
            've_total' => 'nullable|numeric|min:0',
            'vs_total' => 'nullable|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
        ]);

        // Validar si cambió el petitioner_other
        if ($report->petitioner && $report->petitioner->name === 'Otro' && empty($validated['petitioner_other'])) {
            return back()
                ->withErrors(['petitioner_other' => 'Debe especificar la unidad peticionaria.'])
                ->withInput();
        }

        // Subir PDF si existe
        if ($request->hasFile('pdf_report')) {
            if ($report->pdf_report) {
                Storage::disk('public')->delete($report->pdf_report);
            }
            
            $originalName = $request->file('pdf_report')->getClientOriginalName();
            $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
            $validated['pdf_report'] = $request->file('pdf_report')->storeAs('reports', time() . '_' . $sanitizedName, 'public');
        }

        // Mantener campos que no pueden editar
        $validated['petitioner_other'] = $report->petitioner && $report->petitioner->name === 'Otro' 
            ? $validated['petitioner_other'] 
            : $report->petitioner_other;
    }

    $report->update($validated);

    return redirect()->route('reports.show', $report)->with('success', 'Caso actualizado correctamente.');
}

    /**
     * Remove the specified resource from storage.
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
     * Assign a report to a user.
     */
    public function assign(Request $request, Report $report)
    {
        $user = Auth::user();
        
        // Cualquier usuario autenticado puede asignar casos
        // Admin tiene permiso total
       
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
            'status' => $report->isNuevo() ? Report::STATUS_EN_PROCESO : $report->status,
            /*'status' => $report->status === 'nuevo' ? 'en_proceso' : $report->status,*/
        ]);

        return redirect()->back()->with('success', 'Caso asignado correctamente.');
    }

    /**
     * Unassign a report from a user.
     */
    public function unassign(Report $report)
    {
        $user = Auth::user();
        
        // Admin puede desasignar cualquier caso
        // Usuario puede desasignarse solo si está asignado a él mismo
        $canUnassign = $user->role === 'admin' || $report->assigned_to === $user->id;
        
        if (!$canUnassign) {
            abort(403, 'No tienes permiso para desasignar este caso.');
        }

        $report->update([
            'assigned_to' => null,
            'assigned' => false,
        ]);

        return redirect()->back()->with('success', 'Caso desasignado correctamente.');
    }

    /**
     * Self-assign a report to the authenticated user.
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
            'status' => $report->isNuevo() ? Report::STATUS_EN_PROCESO : $report->status,
            /*'status' => $report->status === 'nuevo' ? 'en_proceso' : $report->status,*/
        ]);

        return redirect()->back()->with('success', 'Te has asignado el caso correctamente.');
    }

    /**
     * Finalize a report (close it permanently).
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
     * Reopen a finalized report (admin only).
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
}