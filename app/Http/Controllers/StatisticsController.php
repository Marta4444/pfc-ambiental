<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Species;
use App\Models\ProtectedArea;
use App\Models\Petitioner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    /**
     * Muestra la página principal de estadísticas
     * Accesible por todos los usuarios autenticados
     */
    public function index(Request $request)
    {
        // Filtros
        $dateFrom = $request->input('date_from', Carbon::now()->subMonths(6)->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));
        $categoryId = $request->input('category_id');
        $status = $request->input('status');
        $community = $request->input('community');
        $assignedTo = $request->input('assigned_to');
        $assignmentStatus = $request->input('assignment_status'); 
        $urgency = $request->input('urgency');

        $query = Report::query();
        
        if ($dateFrom) {
            $query->whereDate('reports.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('reports.created_at', '<=', $dateTo);
        }
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($community) {
            $query->where('community', $community);
        }
        if ($assignedTo) {
            $query->where('assigned_to', $assignedTo);
        }
        if ($assignmentStatus === 'assigned') {
            $query->whereNotNull('assigned_to');
        } elseif ($assignmentStatus === 'unassigned') {
            $query->whereNull('assigned_to');
        }
        if ($urgency) {
            $query->where('urgency', $urgency);
        }

        // Clonar la query para diferentes estadísticas
        $baseQuery = clone $query;

        // === ESTADÍSTICAS GENERALES ===
        $totalReports = (clone $baseQuery)->count();
        $totalCost = (clone $baseQuery)->whereNotNull('total_cost')->sum('total_cost');
        $avgCost = $totalReports > 0 ? $totalCost / $totalReports : 0;

        // === POR ESTADO ===
        $reportsByStatus = (clone $baseQuery)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Asegurar que todos los estados estén presentes
        foreach (Report::VALID_STATUSES as $s) {
            if (!isset($reportsByStatus[$s])) {
                $reportsByStatus[$s] = 0;
            }
        }

        // === POR URGENCIA ===
        $reportsByUrgency = (clone $baseQuery)
            ->select('urgency', DB::raw('count(*) as total'))
            ->groupBy('urgency')
            ->pluck('total', 'urgency')
            ->toArray();

        // === POR CATEGORÍA ===
        $reportsByCategory = (clone $baseQuery)
            ->join('categories', 'reports.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('count(*) as total'))
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'name')
            ->toArray();

        // === POR COMUNIDAD AUTÓNOMA ===
        $reportsByCommunity = (clone $baseQuery)
            ->select('community', DB::raw('count(*) as total'))
            ->whereNotNull('community')
            ->where('community', '!=', '')
            ->groupBy('community')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'community')
            ->toArray();

        // === TENDENCIA TEMPORAL (últimos 12 meses) ===
        $monthlyTrend = Report::select(
                DB::raw("DATE_FORMAT(reports.created_at, '%Y-%m') as month"),
                DB::raw('count(*) as total'),
                DB::raw('SUM(CASE WHEN status = "completado" THEN 1 ELSE 0 END) as completed')
            )
            ->where('reports.created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->month => [
                    'total' => $item->total,
                    'completed' => $item->completed
                ]];
            })
            ->toArray();

        // Rellenar meses faltantes
        $allMonths = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $allMonths[$month] = $monthlyTrend[$month] ?? ['total' => 0, 'completed' => 0];
        }

        // === COSTES POR CATEGORÍA ===
        $costsByCategory = (clone $baseQuery)
            ->join('categories', 'reports.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(reports.total_cost) as total_cost'))
            ->whereNotNull('reports.total_cost')
            ->groupBy('categories.name')
            ->orderByDesc('total_cost')
            ->limit(10)
            ->pluck('total_cost', 'name')
            ->toArray();

        // Datos para los filtros
        $categories = Category::orderBy('name')->get();
        $communities = Report::select('community')
            ->whereNotNull('community')
            ->where('community', '!=', '')
            ->distinct()
            ->orderBy('community')
            ->pluck('community');
        $users = User::orderBy('name')->get();
        $statuses = Report::VALID_STATUSES;
        $statusLabels = Report::STATUS_LABELS;
        $urgencies = Report::VALID_URGENCIES;
        $urgencyLabels = Report::URGENCY_LABELS;

        $adminData = [];

        // Si el usuario es admin, cargar estadísticas adicionales
        if (auth()->user()->role === 'admin') {
            $adminData = $this->getAdminStatistics($dateFrom, $dateTo);
        }

        return view('statistics.index', compact(
            'totalReports',
            'totalCost',
            'avgCost',
            'reportsByStatus',
            'reportsByUrgency',
            'reportsByCategory',
            'reportsByCommunity',
            'allMonths',
            'costsByCategory',
            'categories',
            'communities',
            'users',
            'statuses',
            'statusLabels',
            'urgencies',
            'urgencyLabels',
            'dateFrom',
            'dateTo',
            'categoryId',
            'status',
            'community',
            'assignedTo',
            'assignmentStatus',
            'urgency',
            'adminData'
        ));
    }

    /**
     * Obtener estadísticas exclusivas para administradores
     */
    private function getAdminStatistics($dateFrom, $dateTo): array
    {
        // === ESTADÍSTICAS DE AUDITORÍA ===
        $auditStats = $this->buildAuditStats($dateFrom, $dateTo);

        // === ESTADÍSTICAS DEL SISTEMA ===
        
        // Contadores generales del sistema
        $totalCategories = Category::count();
        $totalSubcategories = Subcategory::count();
        $totalUsers = User::count();
        $totalSpecies = Species::count();
        $totalProtectedAreas = ProtectedArea::count();
        $totalPetitioners = Petitioner::count();

        $catUserStats = $this->buildCategoryAndUserStats();

        return array_merge($auditStats, $catUserStats, [
            'totalCategories' => $totalCategories,
            'totalSubcategories' => $totalSubcategories,
            'totalUsers' => $totalUsers,
            'totalSpecies' => $totalSpecies,
            'totalProtectedAreas' => $totalProtectedAreas,
            'totalPetitioners' => $totalPetitioners,
        ]);
    }

    /**
     * Estadísticas de administración (solo admin)
     */
    public function admin(Request $request)
    {
        // Filtros
        $dateFrom = $request->input('date_from', Carbon::now()->subMonths(6)->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));

        $query = Report::query();
        
        if ($dateFrom) {
            $query->whereDate('reports.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('reports.created_at', '<=', $dateTo);
        }

        $baseQuery = clone $query;

        // === ESTADÍSTICAS DE USUARIOS ===
        
        // Casos por usuario asignado
        $reportsByAssignedUser = (clone $baseQuery)
            ->join('users', 'reports.assigned_to', '=', 'users.id')
            ->select('users.name', DB::raw('count(*) as total'))
            ->whereNotNull('reports.assigned_to')
            ->groupBy('users.name')
            ->orderByDesc('total')
            ->pluck('total', 'name')
            ->toArray();

        // Casos completados por usuario
        $completedByUser = (clone $baseQuery)
            ->join('users', 'reports.assigned_to', '=', 'users.id')
            ->select('users.name', DB::raw('count(*) as total'))
            ->whereNotNull('reports.assigned_to')
            ->where('reports.status', Report::STATUS_COMPLETADO)
            ->groupBy('users.name')
            ->orderByDesc('total')
            ->pluck('total', 'name')
            ->toArray();

        // Casos sin asignar
        $unassignedCount = (clone $baseQuery)
            ->whereNull('assigned_to')
            ->count();

        // Tiempo promedio de resolución (días entre creación y completado)
        $avgResolutionTime = Report::whereNotNull('updated_at')
            ->where('status', Report::STATUS_COMPLETADO)
            ->select(DB::raw('AVG(DATEDIFF(updated_at, created_at)) as avg_days'))
            ->value('avg_days') ?? 0;

        // === MÉTRICAS DE RENDIMIENTO ===
        
        // Totales
        $totalReports = (clone $baseQuery)->count();
        $assignedReports = (clone $baseQuery)->whereNotNull('assigned_to')->count();
        $completedReports = (clone $baseQuery)->where('status', Report::STATUS_COMPLETADO)->count();
        $pendingReports = (clone $baseQuery)->whereIn('status', [
            Report::STATUS_NUEVO, 
            Report::STATUS_EN_PROCESO, 
            Report::STATUS_EN_ESPERA
        ])->count();

        // Tasa de completado
        $completionRate = $totalReports > 0 ? ($completedReports / $totalReports) * 100 : 0;

        // === DISTRIBUCIÓN POR URGENCIA ===
        $urgentReports = (clone $baseQuery)->where('urgency', Report::URGENCY_URGENTE)->count();
        $highPriorityReports = (clone $baseQuery)->where('urgency', Report::URGENCY_ALTA)->count();
        $normalReports = (clone $baseQuery)->where('urgency', Report::URGENCY_NORMAL)->count();

        // === COSTES TOTALES ===
        $totalCost = (clone $baseQuery)->whereNotNull('total_cost')->sum('total_cost');
        $vrTotal = (clone $baseQuery)->whereNotNull('vr_total')->sum('vr_total');
        $veTotal = (clone $baseQuery)->whereNotNull('ve_total')->sum('ve_total');
        $vsTotal = (clone $baseQuery)->whereNotNull('vs_total')->sum('vs_total');

        // === ESTADÍSTICAS DE AUDITORÍA ===
        extract($this->buildAuditStats($dateFrom, $dateTo));

        // === ESTADÍSTICAS DE CATEGORÍAS ===
        extract($this->buildCategoryAndUserStats());

        // Top 5 categorías más costosas
        $topCostlyCategories = (clone $baseQuery)
            ->join('categories', 'reports.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(reports.total_cost) as total_cost'))
            ->whereNotNull('reports.total_cost')
            ->groupBy('categories.name')
            ->orderByDesc('total_cost')
            ->limit(5)
            ->pluck('total_cost', 'name')
            ->toArray();

        // === COMUNIDADES AUTÓNOMAS ===
        $communitiesStats = (clone $baseQuery)
            ->select(
                'community',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(total_cost) as total_cost')
            )
            ->whereNotNull('community')
            ->where('community', '!=', '')
            ->groupBy('community')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // === ACTIVIDAD RECIENTE ===
        $recentReports = Report::with(['category', 'assignedTo'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Lista de usuarios para filtros
        $users = User::orderBy('name')->get();

        return view('statistics.admin', compact(
            'reportsByAssignedUser',
            'completedByUser',
            'unassignedCount',
            'avgResolutionTime',
            'totalReports',
            'assignedReports',
            'completedReports',
            'pendingReports',
            'completionRate',
            'urgentReports',
            'highPriorityReports',
            'normalReports',
            'totalCost',
            'vrTotal',
            'veTotal',
            'vsTotal',
            'recentReports',
            'users',
            'dateFrom',
            'dateTo',
            'totalAuditLogs',
            'auditByAction',
            'auditByUser',
            'auditByModelType',
            'auditTrendFull',
            'recentLogins',
            'actionLabels',
            'categoryStats',
            'topCostlyCategories',
            'topSubcategories',
            'userEfficiency',
            'communitiesStats'
        ));
    }

    private function buildAuditStats(string $dateFrom, string $dateTo): array
    {
        $auditQuery = AuditLog::query();
        if ($dateFrom) {
            $auditQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $auditQuery->whereDate('created_at', '<=', $dateTo);
        }

        $totalAuditLogs = (clone $auditQuery)->count();

        $auditByAction = (clone $auditQuery)
            ->select('action', DB::raw('count(*) as total'))
            ->groupBy('action')
            ->orderByDesc('total')
            ->pluck('total', 'action')
            ->toArray();

        $auditByUser = (clone $auditQuery)
            ->select('user_name', DB::raw('count(*) as total'))
            ->whereNotNull('user_name')
            ->groupBy('user_name')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'user_name')
            ->toArray();

        $auditByModelType = (clone $auditQuery)
            ->select('model_type', DB::raw('count(*) as total'))
            ->whereNotNull('model_type')
            ->groupBy('model_type')
            ->orderByDesc('total')
            ->pluck('total', 'model_type')
            ->toArray();

        $auditTrend = AuditLog::select(
                DB::raw("DATE(created_at) as date"),
                DB::raw('count(*) as total')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $auditTrendFull = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $auditTrendFull[$date] = $auditTrend[$date] ?? 0;
        }

        $recentLogins = AuditLog::where('action', AuditLog::ACTION_LOGIN)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        return [
            'totalAuditLogs' => $totalAuditLogs,
            'auditByAction' => $auditByAction,
            'auditByUser' => $auditByUser,
            'auditByModelType' => $auditByModelType,
            'auditTrendFull' => $auditTrendFull,
            'recentLogins' => $recentLogins,
            'actionLabels' => AuditLog::ACTION_LABELS,
        ];
    }

    private function buildCategoryAndUserStats(): array
    {
        $categoryStats = Category::leftJoin('reports', 'categories.id', '=', 'reports.category_id')
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('COUNT(reports.id) as total_reports'),
                DB::raw('SUM(CASE WHEN reports.status = "completado" THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(reports.total_cost) as total_cost')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_reports')
            ->get()
            ->map(function ($cat) {
                $cat->completion_rate = $cat->total_reports > 0
                    ? round(($cat->completed / $cat->total_reports) * 100, 1)
                    : 0;
                return $cat;
            });

        $topSubcategories = Subcategory::join('reports', 'subcategories.id', '=', 'reports.subcategory_id')
            ->join('categories', 'subcategories.category_id', '=', 'categories.id')
            ->select(
                'subcategories.name as subcategory_name',
                'categories.name as category_name',
                DB::raw('COUNT(reports.id) as total')
            )
            ->groupBy('subcategories.id', 'subcategories.name', 'categories.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $userEfficiency = User::leftJoin('reports', function ($join) {
                $join->on('users.id', '=', 'reports.assigned_to')
                     ->where('reports.status', '=', Report::STATUS_COMPLETADO);
            })
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(reports.id) as completed_count'),
                DB::raw('AVG(DATEDIFF(reports.updated_at, reports.created_at)) as avg_days')
            )
            ->where('users.role', '!=', 'admin')
            ->groupBy('users.id', 'users.name')
            ->having('completed_count', '>', 0)
            ->orderByDesc('completed_count')
            ->limit(10)
            ->get();

        return [
            'categoryStats' => $categoryStats,
            'topSubcategories' => $topSubcategories,
            'userEfficiency' => $userEfficiency,
        ];
    }
}
