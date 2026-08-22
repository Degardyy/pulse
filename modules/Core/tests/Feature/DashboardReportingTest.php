<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Database\Seeders\OfficialsSeeder;
use Modules\Core\Database\Seeders\OrganizationSeeder;
use Modules\Core\Database\Seeders\RbacSeeder;
use Modules\Core\Models\Department;
use Modules\Core\Models\Document;
use Modules\Core\Models\Employee;
use Modules\Core\Models\Role;
use Modules\Core\Models\User;
use Tests\TestCase;

class DashboardReportingTest extends TestCase
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

    public function test_recent_documents_widget_shows_only_visible_documents_on_home(): void
    {
        $indriany = $this->userFor('Indriany');
        $alvin = $this->userFor('Alvin Eka M.');

        $this->actingAs($indriany)->post('/documents', [
            'title' => 'Roadmap Infrastruktur IT',
            'file' => UploadedFile::fake()->create('roadmap.pdf', 10, 'application/pdf'),
            'visibility' => Document::VISIBILITY_DEPARTMENT,
            'department_id' => Department::where('code', 'IT')->first()->id,
        ]);

        $this->actingAs($indriany)->get('/dashboard')
            ->assertOk()
            ->assertSee('Dokumen Terbaru')
            ->assertSee('Roadmap Infrastruktur IT');

        $this->actingAs($alvin)->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Roadmap Infrastruktur IT');
    }

    public function test_reports_page_lists_by_permission(): void
    {
        $regular = User::factory()->create();

        $this->actingAs($regular)->get('/reports')
            ->assertOk()
            ->assertSee('Pejabat Struktural')
            ->assertDontSee('Audit Trail');

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('code', Role::CODE_ADMINISTRATOR)->first());

        $this->actingAs($admin)->get('/reports')
            ->assertSee('Pejabat Struktural')
            ->assertSee('Audit Trail');
    }

    public function test_officials_report_streams_csv_with_real_rows(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get('/reports/core.officials/download');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Nama,Jabatan,Level,Status', $csv);
        $this->assertStringContainsString('"Untung Suryadi","President Director",president_director,Definitif', $csv);
        $this->assertStringContainsString('Plt', $csv);
    }

    public function test_audit_report_requires_permission_and_unknown_report_is_404(): void
    {
        $regular = User::factory()->create();

        $this->actingAs($regular)->get('/reports/core.audit-trail/download')->assertForbidden();
        $this->actingAs($regular)->get('/reports/tidak.ada/download')->assertNotFound();

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('code', Role::CODE_ADMINISTRATOR)->first());

        $this->actingAs($admin)->get('/reports/core.audit-trail/download')->assertOk();
    }
}
