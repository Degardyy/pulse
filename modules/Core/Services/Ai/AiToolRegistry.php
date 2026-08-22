<?php

namespace Modules\Core\Services\Ai;

use Closure;
use Modules\Core\Models\User;

/**
 * Explicit allowlist of what AI is permitted to do (ADR-005). A tool is a
 * named, described, authorized service-layer call — never a query. Modules
 * register their AI-callable capabilities here; nothing is discoverable by
 * reflection.
 */
class AiToolRegistry
{
    /** @var array<string, array{description: string, parameters: array<string, string>, permission: ?string, handler: Closure}> */
    private array $tools = [];

    /**
     * @param  array<string, string>  $parameters  name => description (for the LLM later)
     * @param  Closure(User, array<string, mixed>): mixed  $handler
     */
    public function register(
        string $name,
        string $description,
        array $parameters,
        Closure $handler,
        ?string $permission = null,
    ): void {
        $this->tools[$name] = compact('description', 'parameters', 'permission', 'handler');
    }

    /** @return array{description: string, parameters: array<string, string>, permission: ?string, handler: Closure}|null */
    public function get(string $name): ?array
    {
        return $this->tools[$name] ?? null;
    }

    /**
     * Tool schemas available to a given user (what the LLM will see).
     *
     * @return array<string, array{description: string, parameters: array<string, string>}>
     */
    public function schemaFor(User $user): array
    {
        $schemas = [];

        foreach ($this->tools as $name => $tool) {
            if ($tool['permission'] === null || $user->hasPermission($tool['permission'])) {
                $schemas[$name] = [
                    'description' => $tool['description'],
                    'parameters' => $tool['parameters'],
                ];
            }
        }

        return $schemas;
    }
}
