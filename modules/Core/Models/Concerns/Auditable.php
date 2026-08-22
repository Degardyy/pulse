<?php

namespace Modules\Core\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\AuditLog;
use Modules\Core\Services\Audit\AuditService;

/**
 * Automatic audit trail for a model's created/updated/deleted events.
 *
 * Per-model tuning:
 *   protected array $auditExclude = [...];  // attributes never recorded (noise)
 *   protected array $auditMask = [...];     // recorded as ****** (secrets)
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            app(AuditService::class)->record(
                AuditLog::EVENT_CREATED,
                $model,
                null,
                $model->auditValues($model->getAttributes()),
            );
        });

        static::updated(function (Model $model) {
            $new = $model->auditValues($model->getChanges());

            if ($new === []) {
                return;
            }

            $old = array_intersect_key($model->auditValues($model->getOriginal()), $new);

            app(AuditService::class)->record(AuditLog::EVENT_UPDATED, $model, $old, $new);
        });

        static::deleted(function (Model $model) {
            app(AuditService::class)->record(
                AuditLog::EVENT_DELETED,
                $model,
                $model->auditValues($model->getAttributes()),
                null,
            );
        });
    }

    /** Strip noise, drop excluded attributes, mask secrets. */
    public function auditValues(array $attributes): array
    {
        unset($attributes['created_at'], $attributes['updated_at']);

        foreach ($this->auditExclude ?? [] as $key) {
            unset($attributes[$key]);
        }

        foreach ($this->auditMask ?? [] as $key) {
            if (array_key_exists($key, $attributes)) {
                $attributes[$key] = '••••••';
            }
        }

        return $attributes;
    }
}
