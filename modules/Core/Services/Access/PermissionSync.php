<?php

namespace Modules\Core\Services\Access;

use Modules\Core\Models\Permission;

/**
 * Mirrors the code-declared permission registry into core_permissions.
 * Permissions no longer declared by any module are pruned (their role links
 * cascade away); role definitions themselves are never touched.
 */
class PermissionSync
{
    public function __construct(private readonly PermissionRegistry $registry) {}

    /** @return array{synced: int, pruned: int} */
    public function sync(): array
    {
        $declared = $this->registry->all();

        foreach ($declared as $code => $meta) {
            Permission::updateOrCreate(['code' => $code], $meta);
        }

        $pruned = Permission::whereNotIn('code', array_keys($declared))->delete();

        return ['synced' => count($declared), 'pruned' => $pruned];
    }
}
