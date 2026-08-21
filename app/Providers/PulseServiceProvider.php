<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Services\Access\PermissionRegistry;

/**
 * Registers every active PULSE module listed in config/modules.php (ADR-002).
 */
class PulseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Shared by all modules to declare their permissions (ADR-007).
        $this->app->singleton(PermissionRegistry::class);

        foreach (config('modules.modules', []) as $provider) {
            $this->app->register($provider);
        }
    }
}
