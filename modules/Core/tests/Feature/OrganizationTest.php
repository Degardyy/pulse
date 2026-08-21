<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Database\Seeders\OrganizationSeeder;
use Modules\Core\Models\Department;
use Modules\Core\Models\Directorate;
use Modules\Core\Models\Division;
use Modules\Core\Models\User;
use Modules\Core\Services\OrganizationService;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_official_paljaya_structure(): void
    {
        $this->seed(OrganizationSeeder::class);

        $this->assertSame(3, Directorate::count());
        $this->assertSame(12, Division::count());
        $this->assertSame(34, Department::count());

        // Spot checks against the official chart (1 July 2026).
        $this->assertSame('unit', Division::where('code', 'IA')->firstOrFail()->type);
        $this->assertSame(
            'Corporate Strategy',
            Department::where('code', 'MONEV')->firstOrFail()->division->name,
        );
        $this->assertSame(
            'Administration & Finance Director',
            Division::where('code', 'ITP')->firstOrFail()->directorate->name,
        );
        $this->assertSame(6, Division::where('code', 'OPM')->firstOrFail()->departments()->count());
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(OrganizationSeeder::class);
        $this->seed(OrganizationSeeder::class);

        $this->assertSame(3, Directorate::count());
        $this->assertSame(12, Division::count());
        $this->assertSame(34, Department::count());
    }

    public function test_structure_tree_excludes_inactive_units(): void
    {
        $this->seed(OrganizationSeeder::class);
        Division::where('code', 'ITP')->update(['is_active' => false]);

        $service = app(OrganizationService::class);
        $codes = $service->structureTree()->flatMap->divisions->pluck('code');

        $this->assertNotContains('ITP', $codes);
        $this->assertSame(11, $service->counts()['divisions']);
    }

    public function test_organization_page_shows_structure_to_authenticated_user(): void
    {
        $this->seed(OrganizationSeeder::class);

        $this->actingAs(User::factory()->create())
            ->get('/organization')
            ->assertOk()
            ->assertSee('Struktur Organisasi')
            ->assertSee('Information Technology & Procurement')
            ->assertSee('Internal Audit')
            ->assertSee('STP Pulo Gebang');
    }
}
