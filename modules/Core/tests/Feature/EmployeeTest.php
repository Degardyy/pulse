<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Database\Seeders\OfficialsSeeder;
use Modules\Core\Database\Seeders\OrganizationSeeder;
use Modules\Core\Models\Employee;
use Modules\Core\Models\Position;
use Modules\Core\Models\PositionAssignment;
use Modules\Core\Models\User;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected function seedOfficials(): void
    {
        $this->seed(OrganizationSeeder::class);
        $this->seed(OfficialsSeeder::class);
    }

    public function test_seeder_creates_structural_officials_from_official_chart(): void
    {
        $this->seedOfficials();

        // 3 directorate seats + 2 Internal Audit seats + 11 division heads + 34 department heads.
        $this->assertSame(50, Position::count());
        $this->assertSame(40, Employee::count());
        $this->assertSame(45, PositionAssignment::whereNull('ended_at')->count());
        $this->assertSame(7, PositionAssignment::whereNull('ended_at')->where('is_acting', true)->count());

        $vacant = Position::with('currentAssignment')->get()->filter->isVacant()->pluck('code');
        $this->assertEqualsCanonicalizing(
            ['DEPTH-BDEV', 'DEPTH-MEM', 'DEPTH-PROC', 'DEPTH-CFIN', 'DEPTH-HSE'],
            $vacant->all(),
        );
    }

    public function test_one_person_can_hold_two_seats(): void
    {
        $this->seedOfficials();

        $dede = Employee::where('name', 'Dede Sudewa')->firstOrFail();
        $seats = $dede->activeAssignments()->with('position')->get();

        $this->assertCount(2, $seats);
        $this->assertTrue($seats->firstWhere('position.code', 'DIVH-ITP')->is_acting);
        $this->assertFalse($seats->firstWhere('position.code', 'DIVH-HCGA')->is_acting);
    }

    public function test_seeder_is_idempotent_and_reconciles_holder_changes(): void
    {
        $this->seedOfficials();
        $this->seed(OfficialsSeeder::class);

        $this->assertSame(50, Position::count());
        $this->assertSame(40, Employee::count());
        $this->assertSame(45, PositionAssignment::whereNull('ended_at')->count());

        // Simulate an outdated holder: the seeder must end the stale assignment.
        $position = Position::where('code', 'DEPTH-IT')->firstOrFail();
        $outsider = Employee::create(['name' => 'Pejabat Lama']);
        $position->currentAssignment->update(['employee_id' => $outsider->id]);

        $this->seed(OfficialsSeeder::class);

        $this->assertSame('Indriany', $position->fresh()->currentAssignment->employee->name);
        $this->assertNotNull(
            PositionAssignment::where('employee_id', $outsider->id)->first()->ended_at,
        );
    }

    public function test_user_can_be_linked_to_employee(): void
    {
        $this->seedOfficials();

        $employee = Employee::where('name', 'Indriany')->firstOrFail();
        $user = User::factory()->create(['employee_id' => $employee->id]);

        $this->assertSame($employee->id, $user->employee->id);
        $this->assertSame($user->id, $employee->user->id);
    }

    public function test_employees_page_lists_officials_with_seats(): void
    {
        $this->seedOfficials();

        $this->actingAs(User::factory()->create())
            ->get('/employees')
            ->assertOk()
            ->assertSee('Pegawai')
            ->assertSee('Untung Suryadi')
            ->assertSee('Division Head Finance')
            ->assertSee('Plt');
    }

    public function test_organization_page_shows_holders_and_vacancies(): void
    {
        $this->seedOfficials();

        $this->actingAs(User::factory()->create())
            ->get('/organization')
            ->assertOk()
            ->assertSee('Untung Suryadi')
            ->assertSee('Hendry Sitohang')
            ->assertSee('Vacant');
    }

    public function test_employees_page_requires_authentication(): void
    {
        $this->get('/employees')->assertRedirect('/login');
    }
}
