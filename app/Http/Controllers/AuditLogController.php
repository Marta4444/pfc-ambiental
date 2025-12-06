<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Mostrar listado de auditoría
     */
    public function index(Request $request): View
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        // Filtro por usuario
        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        // Filtro por acción
        if ($request->filled('action')) {
            $query->byAction($request->action);
        }

        // Filtro por modelo
        if ($request->filled('model_type')) {
            $query->byModel($request->model_type);
        }

        // Filtro por fecha desde
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }

        // Filtro por fecha hasta
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Búsqueda en descripción
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $audits = $query->paginate(25)->withQueryString();

        // Datos para los filtros
        $users = User::orderBy('name')->get();
        $actions = AuditLog::ACTION_LABELS;
        $modelTypes = [
            'App\Models\Report' => 'Casos',
            'App\Models\ReportDetail' => 'Detalles de caso',
            'App\Models\ReportCostItem' => 'Costes',
            'App\Models\Category' => 'Categorías',
            'App\Models\Subcategory' => 'Subcategorías',
            'App\Models\Field' => 'Campos',
            'App\Models\Petitioner' => 'Peticionarios',
            'App\Models\Species' => 'Especies',
            'App\Models\ProtectedArea' => 'Áreas protegidas',
        ];

        return view('audit.index', compact('audits', 'users', 'actions', 'modelTypes'));
    }

    /**
     * Mostrar detalle de un registro de auditoría
     */
    public function show(AuditLog $auditLog): View
    {
        return view('audit.show', compact('auditLog'));
    }

    /**
     * Mostrar auditoría de un usuario específico
     */
    public function userActivity(User $user): View
    {
        $audits = AuditLog::byUser($user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('audit.user-activity', compact('user', 'audits'));
    }
}