<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Department;
use Modules\Core\Models\Directorate;
use Modules\Core\Models\Division;
use Modules\Core\Models\Employee;
use Modules\Core\Models\Position;
use Modules\Core\Models\PositionAssignment;

/**
 * Structural positions and their office holders per the official chart
 * "Struktur Organisasi (Bhs Inggris), 1 Juli 2026", verified box-by-box from
 * PDF coordinates. Requires OrganizationSeeder to have run first.
 *
 * Holder syntax below: 'Name' definitive, ['Name', true] acting (Plt),
 * null vacant. People holding two seats (e.g. Dede Sudewa, Wenang Adam,
 * Sri Wahyuni, Ismet, Bella Nasila D.) resolve to a single employee record.
 */
class OfficialsSeeder extends Seeder
{
    public function run(): void
    {
        $sort = 0;

        // Directorate-level seats.
        foreach ([
            ['PRESDIR', 'President Director', Position::LEVEL_PRESIDENT_DIRECTOR, 'PD', 'Untung Suryadi'],
            ['DIR-TCD', 'Technical & Commercial Director', Position::LEVEL_DIRECTOR, 'TCD', 'Rizki Shebubakar'],
            ['DIR-AFD', 'Administration & Finance Director', Position::LEVEL_DIRECTOR, 'AFD', 'Doli Rioniari'],
        ] as [$code, $name, $level, $directorateCode, $holder]) {
            $this->seedPosition($code, $name, $level, $holder, $sort++, [
                'directorate_id' => Directorate::where('code', $directorateCode)->firstOrFail()->id,
            ]);
        }

        // Internal Audit unit seats.
        $internalAudit = Division::where('code', 'IA')->firstOrFail();
        $this->seedPosition('IA-HEAD', 'Head Internal Audit', Position::LEVEL_UNIT_HEAD, 'Hendry Sitohang', $sort++, [
            'division_id' => $internalAudit->id,
        ]);
        $this->seedPosition('IA-SEC', 'Secretary Internal Audit', Position::LEVEL_UNIT_SECRETARY, 'Wanny Situmorang', $sort++, [
            'division_id' => $internalAudit->id,
        ]);

        // Division Head seats.
        $divisionHeads = [
            'CSEC' => 'Mala Silva R.',
            'CSTR' => 'Adri Pontianti',
            'HWT' => 'Handry Hanafiah',
            'MCR' => ['Bella Nasila D.', true],
            'OPM' => 'Rahmawati',
            'EPMO' => 'Aldinaufal Octo A.',
            'ITP' => ['Dede Sudewa', true],
            'HCGA' => 'Dede Sudewa',
            'FIN' => 'Moh. Dahril K',
            'GRCHSE' => ['Wenang Adam', true],
            'LGA' => 'Wenang Adam',
        ];

        foreach ($divisionHeads as $divisionCode => $holder) {
            $division = Division::where('code', $divisionCode)->firstOrFail();
            $this->seedPosition(
                "DIVH-{$divisionCode}",
                "Division Head {$division->name}",
                Position::LEVEL_DIVISION_HEAD,
                $holder,
                $sort++,
                ['division_id' => $division->id],
            );
        }

        // Department Head seats.
        $departmentHeads = [
            'PRCSR' => 'Fabian Fernando',
            'IRP' => 'Dira Permata S.',
            'CPLAN' => 'Camelia Indah M.',
            'MONEV' => 'Eliza Sinta T.',
            'BHW' => ['Novarida H.', true],
            'TRANS' => 'Dafid Kurniawan',
            'WSHOP' => 'Hasudungan E. M.',
            'MKTC' => 'Bella Nasila D.',
            'MKTNE' => 'Ismet',
            'MKTSW' => ['Ismet', true],
            'BDEV' => null,
            'CREL' => 'Dali Ichwan',
            'WWTPC' => 'Sri Wahyuni',
            'WWTPO' => ['Sri Wahyuni', true],
            'SEW' => 'Dhiya Ulhaq A.',
            'STPPG' => 'Marlina R. Situmorang',
            'STPDK' => 'Rachmadi Saleh',
            'MEM' => null,
            'TPLAN' => 'Apip Rahman',
            'CONST' => 'M Suko Adi P.',
            'QSQC' => 'Abigael Hotma P.',
            'IT' => 'Indriany',
            'PROC' => null,
            'HC' => ['Tammy Kathlia K.', true],
            'GA' => 'Dwi Noviarita',
            'ACCT' => 'Alvin Eka M.',
            'BILL' => 'Rumintang Eva Y.',
            'BUDT' => 'Fuad Purwanto',
            'CFIN' => null,
            'HSE' => null,
            'GRC' => 'Tonang Kurniawan B.',
            'LAB' => 'Ichwandi Azmir',
            'LEGAL' => 'A.S. Ayuprameswari',
            'ADM' => 'Gusti Leonita S.',
        ];

        foreach ($departmentHeads as $departmentCode => $holder) {
            $department = Department::where('code', $departmentCode)->firstOrFail();
            $this->seedPosition(
                "DEPTH-{$departmentCode}",
                "Department Head {$department->name}",
                Position::LEVEL_DEPARTMENT_HEAD,
                $holder,
                $sort++,
                ['department_id' => $department->id],
            );
        }
    }

    /**
     * Upsert one seat and reconcile its current assignment: end any active
     * assignment that no longer matches the chart, then assign the holder.
     *
     * @param  string|array{0: string, 1: bool}|null  $holder
     * @param  array<string, int>  $unit
     */
    private function seedPosition(string $code, string $name, string $level, string|array|null $holder, int $sort, array $unit): void
    {
        $position = Position::updateOrCreate(
            ['code' => $code],
            ['name' => $name, 'level' => $level, 'sort_order' => $sort] + $unit,
        );

        [$holderName, $isActing] = is_array($holder) ? $holder : [$holder, false];

        $employee = $holderName === null
            ? null
            : Employee::updateOrCreate(['name' => $holderName]);

        PositionAssignment::where('position_id', $position->id)
            ->whereNull('ended_at')
            ->when($employee, fn ($q) => $q->where('employee_id', '!=', $employee->id))
            ->update(['ended_at' => now()]);

        if ($employee !== null) {
            PositionAssignment::updateOrCreate(
                ['position_id' => $position->id, 'employee_id' => $employee->id, 'ended_at' => null],
                ['is_acting' => $isActing],
            );
        }
    }
}
