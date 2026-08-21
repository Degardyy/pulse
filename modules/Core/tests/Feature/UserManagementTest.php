<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Database\Seeders\OfficialsSeeder;
use Modules\Core\Database\Seeders\OrganizationSeeder;
use Modules\Core\Database\Seeders\RbacSeeder;
use Modules\Core\Models\Division;
use Modules\Core\Models\Employee;
use Modules\Core\Models\Role;
use Modules\Core\Models\User;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function itAdmin(): User
    {
        $this->seed(RbacSeeder::class);

        $user = User::factory()->create();
        $user->roles()->attach(Role::where('code', Role::CODE_USER_ADMINISTRATOR)->first());

        return $user;
    }

    public function test_regular_user_cannot_access_user_administration(): void
    {
        $this->seed(RbacSeeder::class);

        $this->actingAs(User::factory()->create())->get('/admin/users')->assertForbidden();
        $this->actingAs(User::factory()->create())->post('/admin/users', [])->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
    }

    public function test_it_admin_sees_user_list(): void
    {
        $admin = $this->itAdmin();
        User::factory()->create(['name' => 'Andi Contoh']);

        $this->actingAs($admin)->get('/admin/users')
            ->assertOk()
            ->assertSee('Pengguna')
            ->assertSee('Andi Contoh');
    }

    public function test_it_admin_can_create_account_with_role_and_employee_link(): void
    {
        $this->seed(OrganizationSeeder::class);
        $this->seed(OfficialsSeeder::class);
        $admin = $this->itAdmin();

        $employee = Employee::where('name', 'Indriany')->firstOrFail();
        $role = Role::where('code', Role::CODE_USER_ADMINISTRATOR)->first();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Indriany',
            'email' => 'indriany@paljaya.co.id',
            'employee_id' => $employee->id,
            'roles' => [$role->id],
        ]);

        $response->assertRedirect(route('core.admin.users.index'))
            ->assertSessionHas('generated_password');

        $created = User::where('email', 'indriany@paljaya.co.id')->firstOrFail();
        $this->assertSame($employee->id, $created->employee_id);
        $this->assertTrue($created->hasPermission('core.users.view'));
        $this->assertTrue(
            Hash::check(session('generated_password'), $created->password),
        );
    }

    public function test_duplicate_email_and_double_linked_employee_are_rejected(): void
    {
        $this->seed(OrganizationSeeder::class);
        $this->seed(OfficialsSeeder::class);
        $admin = $this->itAdmin();

        $employee = Employee::where('name', 'Indriany')->firstOrFail();
        User::factory()->create(['email' => 'dupe@paljaya.co.id', 'employee_id' => $employee->id]);

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'X',
            'email' => 'dupe@paljaya.co.id',
            'employee_id' => $employee->id,
        ])->assertSessionHasErrors(['email', 'employee_id']);
    }

    public function test_it_admin_can_update_account_and_roles(): void
    {
        $admin = $this->itAdmin();
        $target = User::factory()->create();
        $role = Role::where('code', Role::CODE_ADMINISTRATOR)->first();

        $this->actingAs($admin)->put("/admin/users/{$target->id}", [
            'name' => 'Nama Baru',
            'email' => $target->email,
            'roles' => [$role->id],
        ])->assertRedirect(route('core.admin.users.index'));

        $target->refresh();
        $this->assertSame('Nama Baru', $target->name);
        $this->assertTrue($target->roles->contains('code', Role::CODE_ADMINISTRATOR));
    }

    public function test_account_edit_preserves_scoped_role_grants(): void
    {
        $this->seed(OrganizationSeeder::class);
        $admin = $this->itAdmin();

        $division = Division::where('code', 'FIN')->firstOrFail();
        $role = Role::where('code', Role::CODE_USER_ADMINISTRATOR)->first();

        $target = User::factory()->create();
        $target->roles()->attach($role, ['division_id' => $division->id]);

        $this->actingAs($admin)->put("/admin/users/{$target->id}", [
            'name' => $target->name,
            'email' => $target->email,
            'roles' => [],
        ]);

        $this->assertTrue($target->fresh()->hasPermission('core.users.view', $division));
    }

    public function test_reset_password_generates_new_credentials(): void
    {
        $admin = $this->itAdmin();
        $target = User::factory()->create();
        $oldHash = $target->password;

        $this->actingAs($admin)->post("/admin/users/{$target->id}/reset-password")
            ->assertSessionHas('generated_password');

        $target->refresh();
        $this->assertNotSame($oldHash, $target->password);
        $this->assertTrue(Hash::check(session('generated_password'), $target->password));
    }

    public function test_it_admin_can_deactivate_other_but_not_self(): void
    {
        $admin = $this->itAdmin();
        $target = User::factory()->create();

        $this->actingAs($admin)->post("/admin/users/{$target->id}/toggle-active");
        $this->assertFalse($target->fresh()->is_active);

        $this->actingAs($admin)->post("/admin/users/{$admin->id}/toggle-active")->assertForbidden();
        $this->assertTrue($admin->fresh()->is_active);
    }
}
