<?php

namespace Modules\Core\Services\Ai;

use Illuminate\Auth\Access\AuthorizationException;
use InvalidArgumentException;
use Modules\Core\Models\AuditLog;
use Modules\Core\Models\User;
use Modules\Core\Services\Audit\AuditService;

/**
 * The ONLY door between AI and PULSE data (ADR-005):
 * 1. tools come from the explicit allowlist — never raw queries;
 * 2. every call runs AS the requesting user and re-checks their permission;
 * 3. every call is written to the audit trail (tool + arguments).
 *
 * The LLM provider (open question #7) plugs in ABOVE this gateway later:
 * it only ever produces tool calls, which all pass through here.
 */
class AiGateway
{
    public function __construct(
        private readonly AiToolRegistry $registry,
        private readonly AuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $args
     */
    public function call(User $user, string $tool, array $args = []): mixed
    {
        $definition = $this->registry->get($tool);

        if ($definition === null) {
            throw new InvalidArgumentException("AI tool tidak dikenal: {$tool}");
        }

        if ($definition['permission'] !== null && ! $user->hasPermission($definition['permission'])) {
            throw new AuthorizationException("Tidak berwenang memakai AI tool: {$tool}");
        }

        $this->audit->record(AuditLog::EVENT_AI_TOOL_CALL, $user, null, [
            'tool' => $tool,
            'args' => $args,
        ], actor: $user);

        return ($definition['handler'])($user, $args);
    }
}
