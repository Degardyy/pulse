<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Database\Seeders\RbacSeeder;
use Modules\Core\Models\AuditLog;
use Modules\Core\Models\Employee;
use Modules\Core\Models\Role;
use Modules\Core\Models\User;
use Modules\Core\Services\Audit\AuditService;
use Tests\TestCase;

class AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_model_writes_a_created_entry(): void
    {
        $actor = User::factory()->create();
        $this->be($actor);

        $employee = Employee::create(['name' => 'Pegawai Baru']);

        $log = AuditLog::where('event', 'created')
            ->where('auditable_type', Employee::class)
            ->where('auditable_id', $employee->id)
            ->firstOrFail();

        $this->assertSame($actor->id, $log->user_id);
        $this->assertSame('Pegawai Baru', $log->new_values['name']);
        $this->assertNull($log->old_values);
    }

    public function test_updating_records_only_changed_attributes_with_before_and_after(): void
    {
        $employee = Employee::create(['name' => 'Nama Lama']);

        $employee->update(['name' => 'Nama Baru']);

        $log = AuditLog::where('event', 'updated')
            ->where('auditable_id', $employee->id)
            ->where('auditable_type', Employee::class)
            ->firstOrFail();

        $this->assertSame(['name' => 'Nama Lama'], $log->old_values);
        $this->assertSame(['name' => 'Nama Baru'], $log->new_values);
    }

    public function test_deleting_records_the_final_state(): void
    {
        $employee = Employee::create(['name' => 'Akan Dihapus']);
        $employee->delete();

        $log = AuditLog::where('event', 'deleted')->where('auditable_id', $employee->id)->firstOrFail();

        $this->assertSame('Akan Dihapus', $log->old_values['name']);
        $this->assertNull($log->new_values);
    }

    public function test_password_changes_are_masked_and_last_login_is_never_audited(): void
    {
        $user = User::factory()->create();
        AuditLog::query()->delete();

        $user->update(['password' => 'rahasia-baru-123']);

        $log = AuditLog::where('event', 'updated')->where('auditable_id', $user->id)->firstOrFail();
        $this->assertSame('••••••', $log->new_values['password']);
        $this->assertSame('••••••', $log->old_values['password']);

        AuditLog::query()->delete();
        $user->forceFill(['last_login_at' => now()])->save();

        $this->assertSame(0, AuditLog::count());
    }

    public function test_login_and_logout_are_audited(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post('/logout');

        $this->assertTrue(AuditLog::where('event', 'login')->where('user_id', $user->id)->exists());
        $this->assertTrue(AuditLog::where('event', 'logout')->where('user_id', $user->id)->exists());

        $login = AuditLog::where('event', 'login')->first();
        $this->assertNotNull($login->ip_address);
    }

    public function test_role_changes_via_user_management_are_audited(): void
    {
        $this->seed(RbacSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('code', Role::CODE_USER_ADMINISTRATOR)->first());

        $target = User::factory()->create();
        $role = Role::where('code', Role::CODE_ADMINISTRATOR)->first();

        $this->actingAs($admin)->put("/admin/users/{$target->id}", [
            'name' => $target->name,
            'email' => $target->email,
            'roles' => [$role->id],
        ]);

        $log = AuditLog::where('event', 'roles_synced')->where('auditable_id', $target->id)->firstOrFail();

        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame([], $log->old_values['roles']);
        $this->assertSame(['administrator'], $log->new_values['roles']);
    }

    public function test_without_auditing_suppresses_entries(): void
    {
        app(AuditService::class)->withoutAuditing(function () {
            Employee::create(['name' => 'Senyap']);
        });

        $this->assertSame(0, AuditLog::count());

        Employee::create(['name' => 'Tercatat']);
        $this->assertSame(1, AuditLog::count());
    }

    public function test_audit_page_requires_permission(): void
    {
        $this->seed(RbacSeeder::class);

        $this->actingAs(User::factory()->create())->get('/admin/audit')->assertForbidden();
        $this->get('/admin/audit');
    }

    public function test_it_admin_can_view_and_filter_audit_trail(): void
    {
        $this->seed(RbacSeeder::class);
        $admin = User::factory()->create(['name' => 'Petugas IT']);
        $admin->roles()->attach(Role::where('code', Role::CODE_USER_ADMINISTRATOR)->first());

        Employee::create(['name' => 'Objek Audit']);

        $this->actingAs($admin)->get('/admin/audit')
            ->assertOk()
            ->assertSee('Audit Trail')
            ->assertSee('Objek Audit');

        $this->actingAs($admin)->get('/admin/audit?event=deleted')
            ->assertOk()
            ->assertDontSee('Objek Audit');
    }
}
