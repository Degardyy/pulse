<?php

namespace Modules\Core\Services\Audit;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Models\AuditLog;

/**
 * Single writer of the audit trail (append-only). Registered as a singleton so
 * withoutAuditing() suppresses the model observers for the whole request —
 * used by seeders, whose source of truth is a decree document, not a user action.
 */
class AuditService
{
    private bool $enabled = true;

    public function record(string $event, ?Model $subject = null, ?array $old = null, ?array $new = null): void
    {
        if (! $this->enabled) {
            return;
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => $subject?->getMorphClass(),
            'auditable_id' => $subject?->getKey(),
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent() ? mb_substr(request()->userAgent(), 0, 255) : null,
            'created_at' => now(),
        ]);
    }

    /**
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public function withoutAuditing(Closure $callback): mixed
    {
        $this->enabled = false;

        try {
            return $callback();
        } finally {
            $this->enabled = true;
        }
    }
}
