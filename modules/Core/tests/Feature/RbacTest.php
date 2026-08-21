<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Database\Seeders\OrganizationSeeder;
use Modules\Core\Database\Seeders\RbacSeeder;
use Modules\Core\Models\Department;
use Modules\Core\Models\Division;
use Modules\Core\Models\Permission;
use Modules\Core\Models\Role;
use Modules\Core\Models\User;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_declared_permissions_are_synced_to_database(): void
    {
        $this->artisan('pulse:sync-permissions')->assertSuccessful();

        $this->assertTrue(Permission::where('code', 'core.users.manage')->exists());
        $this->assertSame('core', Permission::where('code', 'core.users.view')->first()->module);
    }

    public function test_sync_prunes_permissions_no_longer_declared(): void
    {
        Permission::create(['code' => 'legacy.something', 'name' => 'Legacy', 'module' => 'legacy']);

        $this->artisan('pulse:sync-permissions')->assertSuccessful();

        $this->assertFalse(Permission::where('code', 'legacy.something')->exists());
    }

    public function test_administrator_role_bypasses_permission_checks(): void
    {
        $this->seed(RbacSeeder::class);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('code', Role::CODE_ADMINISTRATOR)->first());

        $this->assertTrue($admin->hasPermission('core.users.manage'));
        $this->assertTrue($admin->can('core.users.view'));
    }

    public function test_user_administrator_has_only_its_permissions(): void
    {
        $this->seed(RbacSeeder::class);

        $itStaff = User::factory()->create();
        $itStaff->roles()->attach(Role::where('code', Role::CODE_USER_ADMINISTRATOR)->first());

        $this->assertTrue($itStaff->hasPermission('core.users.view'));
        $this->assertTrue($itStaff->hasPermission('core.users.manage'));
        $this->assertFalse($itStaff->hasPermission('core.roles.manage'));
    }

    public function test_user_without_roles_has_no_permissions(): void
    {
        $this->seed(RbacSeeder::class);

        $user = User::factory()->create();

        $this->assertFalse($user->hasPermission('core.users.view'));
        $this->assertFalse($user->can('core.users.view'));
    }

    public function test_division_scoped_grant_covers_division_and_its_departments_only(): void
    {
        $this->seed(OrganizationSeeder::class);
        $this->seed(RbacSeeder::class);

        $itp = Division::where('code', 'ITP')->firstOrFail();
        $itDept = Department::where('code', 'IT')->firstOrFail();
        $finance = Division::where('code', 'FIN')->firstOrFail();
        $budgetDept = Department::where('code', 'BUDT')->firstOrFail();

        $user = User::factory()->create();
        $user->roles()->attach(
            Role::where('code', Role::CODE_USER_ADMINISTRATOR)->first(),
            ['division_id' => $itp->id],
        );

        $this->assertTrue($user->hasPermission('core.users.view', $itp));
        $this->assertTrue($user->hasPermission('core.users.view', $itDept));
        $this->assertFalse($user->hasPermission('core.users.view', $finance));
        $this->assertFalse($user->hasPermission('core.users.view', $budgetDept));

        // Least privilege: a scoped grant never satisfies an unscoped check.
        $this->assertFalse($user->hasPermission('core.users.view'));
    }

    public function test_department_scoped_grant_covers_that_department_only(): void
    {
        $this->seed(OrganizationSeeder::class);
        $this->seed(RbacSeeder::class);

        $itDept = Department::where('code', 'IT')->firstOrFail();
        $procDept = Department::where('code', 'PROC')->firstOrFail();
        $itp = Division::where('code', 'ITP')->firstOrFail();

        $user = User::factory()->create();
        $user->roles()->attach(
            Role::where('code', Role::CODE_USER_ADMINISTRATOR)->first(),
            ['department_id' => $itDept->id],
        );

        $this->assertTrue($user->hasPermission('core.users.view', $itDept));
        $this->assertFalse($user->hasPermission('core.users.view', $procDept));
        $this->assertFalse($user->hasPermission('core.users.view', $itp));
        $this->assertFalse($user->hasPermission('core.users.view'));
    }

    public function test_unknown_ability_falls_through_gate_without_permission_grant(): void
    {
        $this->seed(RbacSeeder::class);

        $user = User::factory()->create();

        // Not a declared permission code: Gate::before must not intercept it.
        $this->assertFalse($user->can('totally-unknown-ability'));
    }
}
