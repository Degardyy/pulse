<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Modules\Core\Database\Seeders\OfficialsSeeder;
use Modules\Core\Database\Seeders\OrganizationSeeder;
use Modules\Core\Database\Seeders\RbacSeeder;
use Modules\Core\Models\AuditLog;
use Modules\Core\Models\Department;
use Modules\Core\Models\Document;
use Modules\Core\Models\Employee;
use Modules\Core\Models\Role;
use Modules\Core\Models\User;
use Modules\Core\Services\Ai\AiGateway;
use Modules\Core\Services\Ai\AiToolRegistry;
use Tests\TestCase;

class AiFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(OrganizationSeeder::class);
        $this->seed(OfficialsSeeder::class);
        $this->seed(RbacSeeder::class);
    }

    private function userFor(string $employeeName): User
    {
        return User::factory()->create([
            'employee_id' => Employee::where('name', $employeeName)->firstOrFail()->id,
        ]);
    }

    public function test_unknown_tool_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(AiGateway::class)->call(User::factory()->create(), 'tidak.terdaftar');
    }

    public function test_tool_call_returns_data_and_is_audited_with_the_acting_user(): void
    {
        $user = User::factory()->create();

        $result = app(AiGateway::class)->call($user, 'organization.summary');

        $this->assertSame(3, $result['organisasi']['directorates']);
        $this->assertSame(40, $result['kepegawaian']['employees']);

        $log = AuditLog::where('event', AuditLog::EVENT_AI_TOOL_CALL)->firstOrFail();
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('organization.summary', $log->new_values['tool']);
    }

    public function test_documents_search_respects_the_users_visibility_scope(): void
    {
        $indriany = $this->userFor('Indriany');
        $alvin = $this->userFor('Alvin Eka M.');

        $this->actingAs($indriany)->post('/documents', [
            'title' => 'Panduan Firewall Internal',
            'file' => UploadedFile::fake()->create('fw.pdf', 10, 'application/pdf'),
            'visibility' => Document::VISIBILITY_DEPARTMENT,
            'department_id' => Department::where('code', 'IT')->first()->id,
        ]);

        $gateway = app(AiGateway::class);

        $mine = $gateway->call($indriany, 'documents.search', ['query' => 'Firewall']);
        $theirs = $gateway->call($alvin, 'documents.search', ['query' => 'Firewall']);

        $this->assertCount(1, $mine);
        $this->assertSame('Panduan Firewall Internal', $mine[0]['judul']);
        $this->assertCount(0, $theirs);
    }

    public function test_permission_gated_tool_denies_regular_users(): void
    {
        $gateway = app(AiGateway::class);

        $this->expectException(AuthorizationException::class);
        $gateway->call(User::factory()->create(), 'audit.recent');
    }

    public function test_permission_gated_tool_works_for_authorized_users(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('code', Role::CODE_ADMINISTRATOR)->first());

        $result = app(AiGateway::class)->call($admin, 'audit.recent');

        $this->assertIsArray($result);
        $this->assertNotEmpty($result); // at least this call's own audit entry precursor rows
    }

    public function test_tool_schema_is_filtered_by_permission(): void
    {
        $regular = User::factory()->create();
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('code', Role::CODE_ADMINISTRATOR)->first());

        $registry = app(AiToolRegistry::class);

        $this->assertArrayNotHasKey('audit.recent', $registry->schemaFor($regular));
        $this->assertArrayHasKey('audit.recent', $registry->schemaFor($admin));
        $this->assertArrayHasKey('documents.search', $registry->schemaFor($regular));
    }
}
