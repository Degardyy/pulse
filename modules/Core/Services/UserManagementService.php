<?php

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Modules\Core\Models\AuditLog;
use Modules\Core\Models\User;
use Modules\Core\Services\Audit\AuditService;

/**
 * Account administration (ADR-007; authority: IT department). Initial and
 * reset passwords are generated, shown once to the administrator, and never
 * stored in plain form.
 */
class UserManagementService
{
    public function __construct(private readonly Notifier $notifier) {}

    /** @return Collection<int, User> */
    public function listWithDetails(): Collection
    {
        return User::query()
            ->with(['employee', 'roles'])
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array{name: string, email: string, employee_id?: int|null}  $data
     * @param  list<int>  $roleIds
     * @return array{user: User, password: string}
     */
    public function create(array $data, array $roleIds): array
    {
        $password = Str::password(12);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'employee_id' => $data['employee_id'] ?? null,
            'password' => $password,
            'auth_provider' => User::PROVIDER_LOCAL,
            'is_active' => true,
        ]);

        $this->syncGlobalRoles($user, $roleIds);

        $this->notifier->send(
            $user,
            'Selamat datang di PULSE',
            'Akun Anda telah dibuat. Ini adalah ruang kerja digital Perumda Paljaya.',
            route('core.dashboard'),
        );

        return ['user' => $user, 'password' => $password];
    }

    /**
     * @param  array{name: string, email: string, employee_id?: int|null}  $data
     * @param  list<int>  $roleIds
     */
    public function update(User $user, array $data, array $roleIds): User
    {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'employee_id' => $data['employee_id'] ?? null,
        ]);

        $this->syncGlobalRoles($user, $roleIds);

        return $user;
    }

    public function resetPassword(User $user): string
    {
        $password = Str::password(12);
        $user->update(['password' => $password]);

        $this->notifier->send(
            $user,
            'Password akun Anda di-reset',
            'Administrator me-reset password akun PULSE Anda. Bila ini bukan permintaan Anda, hubungi Department IT.',
            tone: 'warning',
        );

        return $password;
    }

    public function toggleActive(User $user): User
    {
        $user->update(['is_active' => ! $user->is_active]);

        return $user;
    }

    /**
     * Replace the user's GLOBAL role grants only — scoped grants (division/
     * department) are managed by their own modules and must survive account edits.
     *
     * @param  list<int>  $roleIds
     */
    private function syncGlobalRoles(User $user, array $roleIds): void
    {
        $before = $user->roles()
            ->wherePivotNull('division_id')
            ->wherePivotNull('department_id')
            ->pluck('code')->sort()->values();

        $user->roles()->newPivotStatement()
            ->where('user_id', $user->id)
            ->whereNull('division_id')
            ->whereNull('department_id')
            ->delete();

        foreach (array_unique($roleIds) as $roleId) {
            $user->roles()->attach($roleId);
        }

        $user->unsetRelation('roles');

        $after = $user->roles()
            ->wherePivotNull('division_id')
            ->wherePivotNull('department_id')
            ->pluck('code')->sort()->values();

        if ($before->all() !== $after->all()) {
            app(AuditService::class)->record(
                AuditLog::EVENT_ROLES_SYNCED,
                $user,
                ['roles' => $before->all()],
                ['roles' => $after->all()],
            );

            $this->notifier->send(
                $user,
                'Akses Anda diperbarui',
                'Role akun PULSE Anda diubah oleh administrator.',
            );
        }

        $user->unsetRelation('roles');
    }
}
