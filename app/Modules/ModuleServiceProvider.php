<?php

namespace App\Modules;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Modules\Core\Services\Access\PermissionRegistry;

/**
 * Base provider for every PULSE module (see docs/architecture/decisions/ADR-002-module-system.md).
 *
 * A module provider only needs to set $moduleName; routes, views, translations and
 * migrations are then loaded from the conventional locations under modules/<Name>/.
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    /** Studly-case module name matching the directory under modules/. */
    protected string $moduleName;

    /**
     * Permissions this module declares (code => description), synced to the
     * database by `pulse:sync-permissions` (ADR-007).
     *
     * @var array<string, string>
     */
    protected array $permissions = [];

    /** @var list<class-string> Artisan commands this module provides. */
    protected array $commands = [];

    public function boot(): void
    {
        $path = $this->modulePath();
        $namespace = $this->viewNamespace();

        if ($this->permissions !== []) {
            $this->app->make(PermissionRegistry::class)
                ->register($this->viewNamespace(), $this->permissions);
        }

        if ($this->commands !== [] && $this->app->runningInConsole()) {
            $this->commands($this->commands);
        }

        if (is_dir("{$path}/Database/Migrations")) {
            $this->loadMigrationsFrom("{$path}/Database/Migrations");
        }

        if (is_dir("{$path}/resources/views")) {
            $this->loadViewsFrom("{$path}/resources/views", $namespace);
        }

        if (is_dir("{$path}/resources/views/components")) {
            Blade::anonymousComponentPath("{$path}/resources/views/components", $namespace);
        }

        if (is_dir("{$path}/resources/lang")) {
            $this->loadTranslationsFrom("{$path}/resources/lang", $namespace);
        }

        if (file_exists("{$path}/routes/web.php")) {
            Route::middleware('web')->group("{$path}/routes/web.php");
        }

        if (file_exists("{$path}/routes/api.php")) {
            Route::middleware('api')->prefix('api')->group("{$path}/routes/api.php");
        }
    }

    public function modulePath(): string
    {
        return base_path("modules/{$this->moduleName}");
    }

    /** View/lang namespace, e.g. "core" for the Core module ("core::layouts.app"). */
    public function viewNamespace(): string
    {
        return Str::kebab($this->moduleName);
    }
}
