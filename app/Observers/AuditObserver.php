<?php

namespace App\Observers;

use App\Helpers\AuditHelper;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    /**
     * Modelos que deben ser auditados
     */
    protected array $auditableModels = [
        \App\Models\Report::class,
        \App\Models\ReportDetail::class,
        \App\Models\ReportCostItem::class,
        \App\Models\Category::class,
        \App\Models\Subcategory::class,
        \App\Models\Field::class,
        \App\Models\Petitioner::class,
        \App\Models\Species::class,
        \App\Models\ProtectedArea::class,
    ];

    /**
     * Campos a ignorar en los cambios
     */
    protected array $ignoredFields = [
        'updated_at',
        'created_at',
        'remember_token',
    ];

    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        if (!$this->shouldAudit($model)) {
            return;
        }

        AuditHelper::log(
            AuditLog::ACTION_CREATE,
            $this->getDescription($model, 'creado'),
            $model,
            null,
            $this->filterFields($model->getAttributes())
        );
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        if (!$this->shouldAudit($model)) {
            return;
        }

        $oldValues = $this->filterFields($model->getOriginal());
        $newValues = $this->filterFields($model->getChanges());

        // No registrar si no hay cambios relevantes
        if (empty($newValues)) {
            return;
        }

        AuditHelper::log(
            AuditLog::ACTION_UPDATE,
            $this->getDescription($model, 'actualizado'),
            $model,
            $oldValues,
            $newValues
        );
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        if (!$this->shouldAudit($model)) {
            return;
        }

        AuditHelper::log(
            AuditLog::ACTION_DELETE,
            $this->getDescription($model, 'eliminado'),
            $model,
            $this->filterFields($model->getAttributes()),
            null
        );
    }

    /**
     * Verificar si el modelo debe ser auditado
     */
    protected function shouldAudit(Model $model): bool
    {
        return in_array(get_class($model), $this->auditableModels);
    }

    /**
     * Filtrar campos ignorados
     */
    protected function filterFields(array $fields): array
    {
        return array_diff_key($fields, array_flip($this->ignoredFields));
    }

    /**
     * Generar descripción de la acción
     */
    protected function getDescription(Model $model, string $action): string
    {
        $modelName = $this->getModelDisplayName($model);
        $identifier = $this->getModelIdentifier($model);

        return "{$modelName} {$identifier} {$action}";
    }

    /**
     * Obtener nombre legible del modelo
     */
    protected function getModelDisplayName(Model $model): string
    {
        $names = [
            \App\Models\Report::class => 'Caso',
            \App\Models\ReportDetail::class => 'Detalle de caso',
            \App\Models\ReportCostItem::class => 'Coste de caso',
            \App\Models\Category::class => 'Categoría',
            \App\Models\Subcategory::class => 'Subcategoría',
            \App\Models\Field::class => 'Campo',
            \App\Models\Petitioner::class => 'Peticionario',
            \App\Models\Species::class => 'Especie',
            \App\Models\ProtectedArea::class => 'Área protegida',
        ];

        return $names[get_class($model)] ?? class_basename($model);
    }

    /**
     * Obtener identificador legible del modelo
     */
    protected function getModelIdentifier(Model $model): string
    {
        // Intentar obtener un identificador legible
        if (method_exists($model, 'getAuditIdentifier')) {
            return $model->getAuditIdentifier();
        }

        // Campos comunes de identificación
        $identifierFields = ['ip', 'title', 'name', 'key_name', 'scientific_name'];

        foreach ($identifierFields as $field) {
            if (!empty($model->{$field})) {
                return "'{$model->{$field}}'";
            }
        }

        return "#{$model->id}";
    }
}
