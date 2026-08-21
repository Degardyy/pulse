<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Providers\CoreServiceProvider;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    public function test_core_module_is_registered(): void
    {
        $this->assertContains(CoreServiceProvider::class, config('modules.modules'));
        $this->assertNotNull($this->app->getProvider(CoreServiceProvider::class));
    }

    public function test_core_view_namespace_is_loaded(): void
    {
        $this->assertTrue(view()->exists('core::landing'));
    }

    public function test_landing_page_renders(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('PULSE')
            ->assertSee('Paljaya Ultimate Service Ecosystem')
            ->assertSee('Core');
    }
}
