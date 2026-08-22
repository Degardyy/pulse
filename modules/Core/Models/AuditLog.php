<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Append-only. Created exclusively through AuditService — never updated or
 * deleted by application code.
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    public const EVENT_CREATED = 'created';

    public const EVENT_UPDATED = 'updated';

    public const EVENT_DELETED = 'deleted';

    public const EVENT_LOGIN = 'login';

    public const EVENT_LOGOUT = 'logout';

    public const EVENT_ROLES_SYNCED = 'roles_synced';

    protected $table = 'core_audit_logs';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return MorphTo<Model, $this> */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /** Human label of the audited subject, resilient to deleted records. */
    public function subjectLabel(): ?string
    {
        if ($this->auditable_type === null) {
            return null;
        }

        $name = $this->new_values['name'] ?? $this->old_values['name']
            ?? $this->new_values['email'] ?? $this->old_values['email']
            ?? ($this->relationLoaded('auditable') ? ($this->auditable?->name ?? $this->auditable?->email ?? null) : null);

        return class_basename($this->auditable_type).($name ? " — {$name}" : " #{$this->auditable_id}");
    }
}
