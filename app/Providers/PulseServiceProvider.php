<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Registers every active PULSE module listed in config/modules.php (ADR-002).
 */
class PulseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        foreach (config('modules.modules', []) as $provider) {
            $this->app->register($provider);
        }
    }
}
