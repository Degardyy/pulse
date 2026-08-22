<?php

namespace Modules\Core\Providers;

use App\Modules\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Core\Console\Commands\SyncPermissionsCommand;
use Modules\Core\Models\Department;
use Modules\Core\Models\Division;
use Modules\Core\Models\Document;
use Modules\Core\Models\User;
use Modules\Core\Policies\DocumentPolicy;
use Modules\Core\Policies\UserPolicy;
use Modules\Core\Services\Access\AccessService;
use Modules\Core\Services\Access\PermissionRegistry;
use Modules\Core\Services\Audit\AuditService;

class CoreServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Core';

    protected array $permissions = [
        'core.users.view' => 'Melihat daftar akun pengguna',
        'core.users.manage' => 'Mengelola akun pengguna (buat, ubah, nonaktifkan, reset password, atur role)',
        'core.audit.view' => 'Melihat audit trail (jejak perubahan data dan aktivitas login)',
        'core.documents.publish-org' => 'Mempublikasikan dokumen ke seluruh Paljaya',
        'core.documents.manage' => 'Mengelola seluruh dokumen (lihat, unggah ke unit mana pun, hapus)',
    ];

    public function register(): void
    {
        $this->app->singleton(AuditService::class);
    }

    protected array $commands = [
        SyncPermissionsCommand::class,
    ];

    public function boot(): void
    {
        parent::boot();

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);

        // Route every declared permission code through the AccessService
        // (ADR-007). Unknown abilities fall through to policies/gates.
        Gate::before(function (User $user, string $ability, array $arguments = []) {
            if (! $this->app->make(PermissionRegistry::class)->has($ability)) {
                return null;
            }

            $scope = $arguments[0] ?? null;

            return $this->app->make(AccessService::class)->allows(
                $user,
                $ability,
                $scope instanceof Division || $scope instanceof Department ? $scope : null,
            );
        });
    }
}
