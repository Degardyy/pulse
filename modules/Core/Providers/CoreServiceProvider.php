<?php

namespace Modules\Core\Providers;

use App\Modules\ModuleServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Modules\Core\Console\Commands\SyncPermissionsCommand;
use Modules\Core\Events\WorkflowApproved;
use Modules\Core\Events\WorkflowRejected;
use Modules\Core\Listeners\HandleDocumentPublishDecision;
use Modules\Core\Models\Department;
use Modules\Core\Models\Division;
use Modules\Core\Models\Document;
use Modules\Core\Models\User;
use Modules\Core\Policies\DocumentPolicy;
use Modules\Core\Policies\UserPolicy;
use Modules\Core\Services\Access\AccessService;
use Modules\Core\Services\Access\PermissionRegistry;
use Modules\Core\Services\Ai\AiToolRegistry;
use Modules\Core\Services\Audit\AuditService;
use Modules\Core\Services\Dashboard\WidgetRegistry;
use Modules\Core\Services\Reporting\ReportRegistry;

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
        $this->app->singleton(WidgetRegistry::class);
        $this->app->singleton(ReportRegistry::class);
        $this->app->singleton(AiToolRegistry::class);
    }

    protected array $commands = [
        SyncPermissionsCommand::class,
    ];

    public function boot(): void
    {
        parent::boot();

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);

        Event::listen(WorkflowApproved::class, [HandleDocumentPublishDecision::class, 'handleApproved']);
        Event::listen(WorkflowRejected::class, [HandleDocumentPublishDecision::class, 'handleRejected']);

        CoreDashboardProvider::register(
            $this->app->make(WidgetRegistry::class),
            $this->app->make(ReportRegistry::class),
        );

        CoreAiToolsProvider::register(
            $this->app->make(AiToolRegistry::class),
        );

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
