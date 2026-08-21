<?php

namespace Modules\Core\Policies;

use Modules\Core\Models\User;

/**
 * Account administration is a global (unscoped) authority held by the IT
 * department via the User Administrator role (ADR-007).
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('core.users.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('core.users.manage');
    }

    public function update(User $user, User $target): bool
    {
        return $user->hasPermission('core.users.manage');
    }

    public function resetPassword(User $user, User $target): bool
    {
        return $user->hasPermission('core.users.manage');
    }

    public function toggleActive(User $user, User $target): bool
    {
        // Locking yourself out is never allowed, whatever your role.
        return $user->id !== $target->id && $user->hasPermission('core.users.manage');
    }
}
