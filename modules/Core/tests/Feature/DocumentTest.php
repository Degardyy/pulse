<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Modules\Core\Database\Seeders\OfficialsSeeder;
use Modules\Core\Database\Seeders\OrganizationSeeder;
use Modules\Core\Database\Seeders\RbacSeeder;
use Modules\Core\Models\Department;
use Modules\Core\Models\Division;
use Modules\Core\Models\Document;
use Modules\Core\Models\Employee;
use Modules\Core\Models\Role;
use Modules\Core\Models\User;
use Tests\TestCase;

class DocumentTest extends TestCase
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

    /** User linked to the employee holding the given seat. */
    private function userFor(string $employeeName): User
    {
        return User::factory()->create([
            'employee_id' => Employee::where('name', $employeeName)->firstOrFail()->id,
        ]);
    }

    private function upload(User $as, array $overrides = []): TestResponse
    {
        return $this->actingAs($as)->post('/documents', array_merge([
            'title' => 'Dokumen Uji',
            'file' => UploadedFile::fake()->create('panduan.pdf', 120, 'application/pdf'),
            'visibility' => Document::VISIBILITY_DEPARTMENT,
            'department_id' => Department::where('code', 'IT')->firstOrFail()->id,
        ], $overrides));
    }

    public function test_member_can_upload_to_own_department_and_file_is_stored(): void
    {
        $indriany = $this->userFor('Indriany'); // Department Head IT

        $this->upload($indriany)->assertRedirect(route('core.documents.index'));

        $document = Document::firstOrFail();
        $this->assertSame('Dokumen Uji', $document->title);
        $this->assertSame(Department::where('code', 'IT')->first()->id, $document->department_id);
        Storage::assertExists($document->file_path);
    }

    public function test_department_document_visibility_matrix(): void
    {
        $indriany = $this->userFor('Indriany');
        $this->upload($indriany);
        $document = Document::firstOrFail();

        // Division head above IT (Dede Sudewa, DIVH-ITP) reads down.
        $dede = $this->userFor('Dede Sudewa');
        // Member of a DIFFERENT department in a different division.
        $alvin = $this->userFor('Alvin Eka M.'); // Accounting & Tax (FIN)

        $this->actingAs($indriany)->get("/documents/{$document->id}/download")->assertOk();
        $this->actingAs($dede)->get("/documents/{$document->id}/download")->assertOk();
        $this->actingAs($alvin)->get("/documents/{$document->id}/download")->assertForbidden();

        $this->actingAs($alvin)->get('/documents')->assertOk()->assertDontSee('Dokumen Uji');
        $this->actingAs($dede)->get('/documents')->assertOk()->assertSee('Dokumen Uji');
    }

    public function test_division_document_is_visible_to_all_division_members_only(): void
    {
        $dede = $this->userFor('Dede Sudewa'); // Division Head ITP
        $itp = Division::where('code', 'ITP')->firstOrFail();

        $this->upload($dede, [
            'title' => 'Kebijakan Division ITP',
            'visibility' => Document::VISIBILITY_DIVISION,
            'division_id' => $itp->id,
            'department_id' => null,
        ])->assertRedirect(route('core.documents.index'));

        $document = Document::firstOrFail();
        $indriany = $this->userFor('Indriany');      // dept IT ∈ ITP
        $alvin = $this->userFor('Alvin Eka M.');     // FIN — outside

        $this->actingAs($indriany)->get("/documents/{$document->id}/download")->assertOk();
        $this->actingAs($alvin)->get("/documents/{$document->id}/download")->assertForbidden();
    }

    public function test_paljaya_document_is_visible_to_everyone_but_needs_permission_to_publish(): void
    {
        $indriany = $this->userFor('Indriany');

        // Without org-publish permission → 403.
        $this->upload($indriany, [
            'visibility' => Document::VISIBILITY_PALJAYA, 'department_id' => null,
        ])->assertForbidden();

        // Administrator (super) publishes org-wide.
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('code', Role::CODE_ADMINISTRATOR)->first());

        $this->upload($admin, [
            'title' => 'Pengumuman Direksi',
            'visibility' => Document::VISIBILITY_PALJAYA, 'department_id' => null,
        ])->assertRedirect(route('core.documents.index'));

        $document = Document::firstOrFail();
        $unrelated = $this->userFor('Alvin Eka M.');

        $this->actingAs($unrelated)->get("/documents/{$document->id}/download")->assertOk();
        $this->actingAs($unrelated)->get('/documents')->assertSee('Pengumuman Direksi');
    }

    public function test_cannot_upload_to_a_department_the_user_does_not_belong_to(): void
    {
        $alvin = $this->userFor('Alvin Eka M.'); // FIN — not IT

        $this->upload($alvin)->assertForbidden();
        $this->assertSame(0, Document::count());
    }

    public function test_upload_notifies_unit_members_but_not_uploader(): void
    {
        $indriany = $this->userFor('Indriany');
        $dede = $this->userFor('Dede Sudewa'); // division head above IT
        $alvin = $this->userFor('Alvin Eka M.');

        $this->upload($indriany);

        $this->assertSame(0, $indriany->notifications()->count());
        $this->assertSame(1, $dede->notifications()->count());
        $this->assertSame(0, $alvin->notifications()->count());
        $this->assertSame('Dokumen baru dibagikan', $dede->notifications()->first()->data['title']);
    }

    public function test_uploader_can_delete_and_file_is_removed(): void
    {
        $indriany = $this->userFor('Indriany');
        $this->upload($indriany);
        $document = Document::firstOrFail();

        $other = $this->userFor('Dede Sudewa');
        $this->actingAs($other)->delete("/documents/{$document->id}")->assertForbidden();

        $this->actingAs($indriany)->delete("/documents/{$document->id}")
            ->assertRedirect(route('core.documents.index'));

        $this->assertSame(0, Document::count());
        Storage::assertMissing($document->file_path);
    }

    public function test_oversized_or_unsupported_files_are_rejected(): void
    {
        $indriany = $this->userFor('Indriany');

        $this->upload($indriany, [
            'file' => UploadedFile::fake()->create('besar.pdf', 21000, 'application/pdf'),
        ])->assertSessionHasErrors('file');

        $this->upload($indriany, [
            'file' => UploadedFile::fake()->create('script.exe', 10, 'application/octet-stream'),
        ])->assertSessionHasErrors('file');
    }

    public function test_guest_is_redirected(): void
    {
        $this->get('/documents')->assertRedirect('/login');
    }
}
