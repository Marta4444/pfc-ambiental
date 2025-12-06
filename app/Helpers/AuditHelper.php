<?php

namespace App\Helpers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditHelper
{
    /**
     * Registrar una acción en el audit log
     */
    public static function log(
        string $action,
        string $description,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null
    ): AuditLog {
        $user = Auth::user();

        return AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'Sistema',
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Registrar inicio de sesión
     */
    public static function logLogin(): AuditLog
    {
        return self::log(
            AuditLog::ACTION_LOGIN,
            'Usuario inició sesión'
        );
    }

    /**
     * Registrar cierre de sesión
     */
    public static function logLogout(): AuditLog
    {
        return self::log(
            AuditLog::ACTION_LOGOUT,
            'Usuario cerró sesión'
        );
    }

    /**
     * Registrar asignación de caso
     */
    public static function logAssign(Model $report, ?string $assignedToName = null): AuditLog
    {
        $description = $assignedToName
            ? "Caso '{$report->ip}' asignado a {$assignedToName}"
            : "Caso '{$report->ip}' asignado";

        return self::log(
            AuditLog::ACTION_ASSIGN,
            $description,
            $report
        );
    }

    /**
     * Registrar auto-asignación de caso
     */
    public static function logSelfAssign(Model $report): AuditLog
    {
        return self::log(
            AuditLog::ACTION_SELF_ASSIGN,
            "Usuario se auto-asignó el caso '{$report->ip}'",
            $report
        );
    }

    /**
     * Registrar desasignación de caso
     */
    public static function logUnassign(Model $report): AuditLog
    {
        return self::log(
            AuditLog::ACTION_UNASSIGN,
            "Caso '{$report->ip}' desasignado",
            $report
        );
    }

    /**
     * Registrar cálculo de costes
     */
    public static function logCalculateCosts(Model $report, array $results): AuditLog
    {
        return self::log(
            AuditLog::ACTION_CALCULATE_COSTS,
            "Costes calculados para caso '{$report->ip}'",
            $report,
            null,
            [
                'groups_processed' => $results['groups_processed'] ?? 0,
                'items_created' => $results['items_created'] ?? 0,
                'total' => $results['totals']['total'] ?? 0,
            ]
        );
    }

    /**
     * Registrar reseteo de costes
     */
    public static function logResetCosts(Model $report): AuditLog
    {
        return self::log(
            AuditLog::ACTION_RESET_COSTS,
            "Costes reseteados para caso '{$report->ip}'",
            $report
        );
    }

    /**
     * Registrar exportación
     */
    public static function logExport(string $type, ?array $filters = null): AuditLog
    {
        return self::log(
            AuditLog::ACTION_EXPORT,
            "Exportación de {$type} realizada",
            null,
            null,
            null,
            ['type' => $type, 'filters' => $filters]
        );
    }

    /**
     * Registrar acción personalizada
     */
    public static function logCustom(
        string $action,
        string $description,
        ?Model $model = null,
        ?array $metadata = null
    ): AuditLog {
        return self::log($action, $description, $model, null, null, $metadata);
    }
}