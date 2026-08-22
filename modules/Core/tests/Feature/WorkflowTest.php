<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Database\Seeders\OfficialsSeeder;
use Modules\Core\Database\Seeders\OrganizationSeeder;
use Modules\Core\Database\Seeders\RbacSeeder;
use Modules\Core\Database\Seeders\WorkflowSeeder;
use Modules\Core\Models\Document;
use Modules\Core\Models\Employee;
use Modules\Core\Models\Role;
use Modules\Core\Models\User;
use Modules\Core\Models\WorkflowDefinition;
use Modules\Core\Models\WorkflowInstance;
use Modules\Core\Models\WorkflowStep;
use Modules\Core\Services\Workflow\WorkflowService;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(OrganizationSeeder::class);
        $this->seed(OfficialsSeeder::class);
        $this->seed(RbacSeeder::class);
        $this->seed(WorkflowSeeder::class);
    }

    private function userFor(string $employeeName): User
    {
        return User::factory()->create([
            'employee_id' => Employee::where('name', $employeeName)->firstOrFail()->id,
        ]);
    }

    /** Indriany (IT) requests org-wide publication of a document. */
    private function requestOrgPublication(): array
    {
        $indriany = $this->userFor('Indriany');

        $this->actingAs($indriany)->post('/documents', [
            'title' => 'Kebijakan Keamanan Informasi',
            'file' => UploadedFile::fake()->create('kebijakan.pdf', 100, 'application/pdf'),
            'visibility' => Document::VISIBILITY_PALJAYA,
        ]);

        // Consume the flash message so it doesn't leak into the next
        // request of this shared test session.
        $this->actingAs($indriany)->get('/documents');

        return [$indriany, Document::firstOrFail()];
    }

    public function test_org_publication_without_permission_starts_approval_workflow(): void
    {
        [$indriany, $document] = $this->requestOrgPublication();

        $this->assertSame(Document::STATUS_PENDING_APPROVAL, $document->status);

        $instance = WorkflowInstance::firstOrFail();
        $this->assertSame($indriany->id, $instance->requested_by);
        $this->assertSame('Persetujuan Corporate Secretary', $instance->currentStep()->name);
        $this->assertSame('DIVH-CSEC', $instance->currentStep()->position->code);
    }

    public function test_pending_document_is_hidden_from_others_but_reviewable_by_approver(): void
    {
        // Approver's account must exist before the request to receive the notification.
        $mala = $this->userFor('Mala Silva R.');   // Division Head Corporate Secretary
        $alvin = $this->userFor('Alvin Eka M.');   // unrelated

        [, $document] = $this->requestOrgPublication();

        $this->actingAs($alvin)->get('/documents')->assertDontSee('Kebijakan Keamanan Informasi');
        $this->actingAs($alvin)->get("/documents/{$document->id}/download")->assertForbidden();

        $this->actingAs($mala)->get("/documents/{$document->id}/download")->assertOk();
        $this->actingAs($mala)->get('/approvals')->assertOk()->assertSee('Kebijakan Keamanan Informasi');

        $this->assertSame('Persetujuan menunggu Anda', $mala->notifications()->first()->data['title']);
    }

    public function test_approval_publishes_the_document_and_notifies_everyone(): void
    {
        [$indriany, $document] = $this->requestOrgPublication();
        $mala = $this->userFor('Mala Silva R.');
        $alvin = $this->userFor('Alvin Eka M.');
        $instance = WorkflowInstance::firstOrFail();

        $this->actingAs($mala)->post("/approvals/{$instance->id}/approve", ['note' => 'Silakan.'])
            ->assertRedirect(route('core.approvals.index'));

        $this->assertSame(Document::STATUS_PUBLISHED, $document->fresh()->status);
        $this->assertSame(WorkflowInstance::STATUS_APPROVED, $instance->fresh()->status);

        // Now readable org-wide; requester notified of approval; audience notified of the doc.
        $this->actingAs($alvin)->get("/documents/{$document->id}/download")->assertOk();
        $titles = $indriany->notifications()->pluck('data')->pluck('title');
        $this->assertContains('Permintaan Anda disetujui', $titles);
        $this->assertContains('Dokumen baru dibagikan', $alvin->notifications()->pluck('data')->pluck('title'));
    }

    public function test_rejection_marks_document_rejected_and_informs_requester_with_note(): void
    {
        [$indriany, $document] = $this->requestOrgPublication();
        $mala = $this->userFor('Mala Silva R.');
        $alvin = $this->userFor('Alvin Eka M.');
        $instance = WorkflowInstance::firstOrFail();

        $this->actingAs($mala)->post("/approvals/{$instance->id}/reject", ['note' => 'Perlu revisi bab 2.']);

        $this->assertSame(Document::STATUS_REJECTED, $document->fresh()->status);
        $this->actingAs($alvin)->get("/documents/{$document->id}/download")->assertForbidden();

        $rejection = $indriany->notifications()->get()->firstWhere('data.title', 'Permintaan Anda ditolak');
        $this->assertStringContainsString('Perlu revisi bab 2.', $rejection->data['body']);
    }

    public function test_non_approver_cannot_decide_and_decisions_are_final(): void
    {
        [, $document] = $this->requestOrgPublication();
        $instance = WorkflowInstance::firstOrFail();

        $alvin = $this->userFor('Alvin Eka M.');
        $this->actingAs($alvin)->post("/approvals/{$instance->id}/approve")->assertForbidden();

        $mala = $this->userFor('Mala Silva R.');
        $this->actingAs($mala)->post("/approvals/{$instance->id}/approve");
        // Second decision on a settled instance is refused.
        $this->actingAs($mala)->post("/approvals/{$instance->id}/reject")->assertForbidden();

        $this->assertSame(Document::STATUS_PUBLISHED, $document->fresh()->status);
    }

    public function test_multi_step_workflow_advances_sequentially(): void
    {
        $definition = WorkflowDefinition::create([
            'code' => 'test.dua-langkah', 'name' => 'Uji Dua Langkah', 'is_active' => true,
        ]);
        $definition->steps()->createMany([
            ['sort_order' => 1, 'name' => 'Kepala Division', 'approver_type' => WorkflowStep::APPROVER_DIVISION_HEAD],
            ['sort_order' => 2, 'name' => 'Administrator', 'approver_type' => WorkflowStep::APPROVER_ROLE, 'approver_value' => Role::CODE_ADMINISTRATOR],
        ]);

        $indriany = $this->userFor('Indriany');
        $dede = $this->userFor('Dede Sudewa');            // Division Head ITP
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('code', Role::CODE_ADMINISTRATOR)->first());

        $subject = Employee::where('name', 'Indriany')->first(); // any model works as subject
        $service = app(WorkflowService::class);
        $instance = $service->start('test.dua-langkah', $subject, $indriany);

        // Step 1: only the division head; admin (step 2) not yet eligible.
        $this->assertTrue($service->pendingFor($dede)->contains('id', $instance->id));
        $this->assertFalse($service->pendingFor($admin)->contains('id', $instance->id));

        $service->approve($instance, $dede);
        $instance = $instance->fresh('instanceSteps');
        $this->assertSame(WorkflowInstance::STATUS_PENDING, $instance->status);
        $this->assertSame('Administrator', $instance->currentStep()->name);
        $this->assertTrue($service->pendingFor($admin)->contains('id', $instance->id));

        $service->approve($instance, $admin);
        $this->assertSame(WorkflowInstance::STATUS_APPROVED, $instance->fresh()->status);
    }

    public function test_direct_publisher_skips_workflow_entirely(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('code', Role::CODE_ADMINISTRATOR)->first());

        $this->actingAs($admin)->post('/documents', [
            'title' => 'Pengumuman Langsung',
            'file' => UploadedFile::fake()->create('info.pdf', 10, 'application/pdf'),
            'visibility' => Document::VISIBILITY_PALJAYA,
        ]);

        $this->assertSame(Document::STATUS_PUBLISHED, Document::first()->status);
        $this->assertSame(0, WorkflowInstance::count());
    }
}
