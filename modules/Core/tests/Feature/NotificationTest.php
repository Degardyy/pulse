<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Database\Seeders\RbacSeeder;
use Modules\Core\Models\Role;
use Modules\Core\Models\User;
use Modules\Core\Services\Notifier;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function itAdmin(): User
    {
        $this->seed(RbacSeeder::class);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('code', Role::CODE_USER_ADMINISTRATOR)->first());

        return $admin;
    }

    public function test_notifier_delivers_in_app_notification(): void
    {
        $user = User::factory()->create();

        app(Notifier::class)->send($user, 'Judul Uji', 'Isi pesan', '/dashboard', 'warning');

        $notification = $user->notifications()->firstOrFail();
        $this->assertSame('Judul Uji', $notification->data['title']);
        $this->assertSame('warning', $notification->data['tone']);
        $this->assertNull($notification->read_at);
    }

    public function test_new_account_receives_welcome_notification(): void
    {
        $admin = $this->itAdmin();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Pegawai Baru',
            'email' => 'baru@paljaya.co.id',
        ]);

        $created = User::where('email', 'baru@paljaya.co.id')->firstOrFail();
        $this->assertSame('Selamat datang di PULSE', $created->notifications()->first()->data['title']);
    }

    public function test_role_change_and_password_reset_notify_the_target(): void
    {
        $admin = $this->itAdmin();
        $target = User::factory()->create();
        $role = Role::where('code', Role::CODE_ADMINISTRATOR)->first();

        $this->actingAs($admin)->put("/admin/users/{$target->id}", [
            'name' => $target->name, 'email' => $target->email, 'roles' => [$role->id],
        ]);
        $this->actingAs($admin)->post("/admin/users/{$target->id}/reset-password");

        $titles = $target->notifications()->pluck('data')->pluck('title');
        $this->assertContains('Akses Anda diperbarui', $titles);
        $this->assertContains('Password akun Anda di-reset', $titles);
    }

    public function test_unchanged_roles_do_not_notify(): void
    {
        $admin = $this->itAdmin();
        $target = User::factory()->create();

        $this->actingAs($admin)->put("/admin/users/{$target->id}", [
            'name' => $target->name, 'email' => $target->email, 'roles' => [],
        ]);

        $this->assertSame(0, $target->notifications()->count());
    }

    public function test_reading_a_notification_marks_it_and_follows_its_url(): void
    {
        $user = User::factory()->create();
        app(Notifier::class)->send($user, 'Buka dashboard', url: route('core.dashboard'));

        $notification = $user->notifications()->first();

        $this->actingAs($user)
            ->post(route('core.notifications.read', $notification->id))
            ->assertRedirect(route('core.dashboard'));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_users_cannot_read_others_notifications(): void
    {
        $owner = User::factory()->create();
        app(Notifier::class)->send($owner, 'Milik orang lain');
        $notification = $owner->notifications()->first();

        $this->actingAs(User::factory()->create())
            ->post(route('core.notifications.read', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_mark_all_read(): void
    {
        $user = User::factory()->create();
        app(Notifier::class)->send($user, 'Satu');
        app(Notifier::class)->send($user, 'Dua');

        $this->actingAs($user)->from('/notifications')->post(route('core.notifications.read-all'));

        $this->assertSame(0, $user->unreadNotifications()->count());
    }

    public function test_notification_page_lists_own_notifications(): void
    {
        $user = User::factory()->create();
        app(Notifier::class)->send($user, 'Pengumuman Penting', 'Detail pengumuman');

        $this->actingAs($user)->get('/notifications')
            ->assertOk()
            ->assertSee('Pengumuman Penting')
            ->assertSee('Detail pengumuman');
    }

    public function test_unread_badge_appears_in_shell(): void
    {
        $user = User::factory()->create();
        app(Notifier::class)->send($user, 'Belum dibaca');

        $this->actingAs($user)->get('/dashboard')
            ->assertSee('belum dibaca', false);
    }
}
