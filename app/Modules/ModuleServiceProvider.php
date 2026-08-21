<?php

namespace App\Modules;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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

    public function boot(): void
    {
        $path = $this->modulePath();
        $namespace = $this->viewNamespace();

        if (is_dir("{$path}/database/migrations")) {
            $this->loadMigrationsFrom("{$path}/database/migrations");
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
