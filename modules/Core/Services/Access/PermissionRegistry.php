<?php

namespace Modules\Core\Services\Access;

/**
 * In-memory registry of every permission declared by the active modules
 * (ADR-007). Code is the source of truth; the database copy exists so roles
 * can reference permissions relationally and is written by PermissionSync.
 *
 * Bound as a singleton; module providers register their declarations at boot.
 */
class PermissionRegistry
{
    /** @var array<string, array{name: string, module: string}> */
    private array $permissions = [];

    /** @param array<string, string> $permissions map of code => human description */
    public function register(string $module, array $permissions): void
    {
        foreach ($permissions as $code => $name) {
            $this->permissions[$code] = ['name' => $name, 'module' => $module];
        }
    }

    public function has(string $code): bool
    {
        return isset($this->permissions[$code]);
    }

    /** @return array<string, array{name: string, module: string}> */
    public function all(): array
    {
        return $this->permissions;
    }
}
