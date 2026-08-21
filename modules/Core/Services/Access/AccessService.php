<?php

namespace Modules\Core\Services\Access;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Modules\Core\Models\Department;
use Modules\Core\Models\Division;
use Modules\Core\Models\User;

/**
 * Single decision point for permission checks (ADR-007).
 *
 * Scope semantics of a role grant:
 * - global grant (no scope)      → applies everywhere, including unscoped checks;
 * - division-scoped grant        → applies to that division and all its departments;
 * - department-scoped grant      → applies to that department only.
 * An UNSCOPED check (no context passed) is satisfied only by a global grant —
 * least privilege: scoped authority never leaks into global functions.
 */
class AccessService
{
    public function allows(User $user, string $code, Division|Department|null $scope = null): bool
    {
        $user->loadMissing('roles.permissions');

        foreach ($user->roles as $role) {
            if (! $this->scopeMatches($role->pivot, $scope)) {
                continue;
            }

            if ($role->is_super || $role->permissions->contains('code', $code)) {
                return true;
            }
        }

        return false;
    }

    private function scopeMatches(Pivot $grant, Division|Department|null $scope): bool
    {
        $grantDivision = $grant->division_id === null ? null : (int) $grant->division_id;
        $grantDepartment = $grant->department_id === null ? null : (int) $grant->department_id;

        if ($grantDivision === null && $grantDepartment === null) {
            return true;
        }

        if ($scope instanceof Department) {
            return $grantDepartment === $scope->id
                || ($grantDepartment === null && $grantDivision === $scope->division_id);
        }

        if ($scope instanceof Division) {
            return $grantDepartment === null && $grantDivision === $scope->id;
        }

        return false;
    }
}
