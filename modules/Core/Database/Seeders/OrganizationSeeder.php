<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Department;
use Modules\Core\Models\Directorate;
use Modules\Core\Models\Division;

/**
 * Official organization structure of Perumda Paljaya per 1 July 2026
 * ("Struktur Organisasi Bhs Inggris, 1 Juli 2026").
 *
 * Not modeled here: Supervisory Board (governance organ, outside the executive
 * line) and President Director Experts (advisory, not an org unit). Office
 * holders are employee data and are seeded in the Employee iteration.
 */
class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $structure = [
            [
                'code' => 'PD',
                'name' => 'President Director',
                'divisions' => [
                    ['code' => 'IA', 'name' => 'Internal Audit', 'type' => Division::TYPE_UNIT, 'departments' => []],
                    ['code' => 'CSEC', 'name' => 'Corporate Secretary', 'departments' => [
                        ['code' => 'PRCSR', 'name' => 'Public Relation & CSR'],
                        ['code' => 'IRP', 'name' => 'Institutional Relations & Protocol'],
                    ]],
                    ['code' => 'CSTR', 'name' => 'Corporate Strategy', 'departments' => [
                        ['code' => 'CPLAN', 'name' => 'Corporate Planning'],
                        ['code' => 'MONEV', 'name' => 'Monitoring & Evaluation'],
                    ]],
                ],
            ],
            [
                'code' => 'TCD',
                'name' => 'Technical & Commercial Director',
                'divisions' => [
                    ['code' => 'HWT', 'name' => 'Hazardous Waste & Transportation', 'departments' => [
                        ['code' => 'BHW', 'name' => 'Biopal & Hazardous Waste'],
                        ['code' => 'TRANS', 'name' => 'Transportation'],
                        ['code' => 'WSHOP', 'name' => 'Workshop'],
                    ]],
                    ['code' => 'MCR', 'name' => 'Marketing & Customer Relation', 'departments' => [
                        ['code' => 'MKTC', 'name' => 'Key Account & Central Jakarta Marketing Area'],
                        ['code' => 'MKTNE', 'name' => 'North Jakarta & East Jakarta Marketing Area'],
                        ['code' => 'MKTSW', 'name' => 'South Jakarta & West Jakarta Marketing Area'],
                        ['code' => 'BDEV', 'name' => 'Business Development'],
                        ['code' => 'CREL', 'name' => 'Customer Relation'],
                    ]],
                    ['code' => 'OPM', 'name' => 'Operational & Maintenance', 'departments' => [
                        ['code' => 'WWTPC', 'name' => 'WWTP Centralized'],
                        ['code' => 'WWTPO', 'name' => 'WWTP On-Site'],
                        ['code' => 'SEW', 'name' => 'Sewerage'],
                        ['code' => 'STPPG', 'name' => 'STP Pulo Gebang'],
                        ['code' => 'STPDK', 'name' => 'STP Duri Kosambi'],
                        ['code' => 'MEM', 'name' => 'Mechanical & Electrical Maintenance'],
                    ]],
                    ['code' => 'EPMO', 'name' => 'Engineering & Project Management Office (PMO)', 'departments' => [
                        ['code' => 'TPLAN', 'name' => 'Technical Planning'],
                        ['code' => 'CONST', 'name' => 'Construction'],
                        ['code' => 'QSQC', 'name' => 'Quantity Surveyor, Quality Assurance & Contract Management'],
                    ]],
                ],
            ],
            [
                'code' => 'AFD',
                'name' => 'Administration & Finance Director',
                'divisions' => [
                    ['code' => 'ITP', 'name' => 'Information Technology & Procurement', 'departments' => [
                        ['code' => 'IT', 'name' => 'Information Technology'],
                        ['code' => 'PROC', 'name' => 'Procurement'],
                    ]],
                    ['code' => 'HCGA', 'name' => 'Human Capital & General Affair', 'departments' => [
                        ['code' => 'HC', 'name' => 'Human Capital'],
                        ['code' => 'GA', 'name' => 'General Affair'],
                    ]],
                    ['code' => 'FIN', 'name' => 'Finance', 'departments' => [
                        ['code' => 'ACCT', 'name' => 'Accounting & Tax'],
                        ['code' => 'BILL', 'name' => 'Billing & Collection'],
                        ['code' => 'BUDT', 'name' => 'Budget & Treasury'],
                        ['code' => 'CFIN', 'name' => 'Corporate Finance'],
                    ]],
                    ['code' => 'GRCHSE', 'name' => 'GRC & HSE', 'departments' => [
                        ['code' => 'HSE', 'name' => 'Health, Safety, & Environment (HSE)'],
                        ['code' => 'GRC', 'name' => 'Governance, Risk, & Compliance (GRC)'],
                        ['code' => 'LAB', 'name' => 'Laboratory'],
                    ]],
                    ['code' => 'LGA', 'name' => 'Legal & Administration', 'departments' => [
                        ['code' => 'LEGAL', 'name' => 'Legal'],
                        ['code' => 'ADM', 'name' => 'Administration'],
                    ]],
                ],
            ],
        ];

        foreach ($structure as $dirOrder => $directorateData) {
            $directorate = Directorate::updateOrCreate(
                ['code' => $directorateData['code']],
                ['name' => $directorateData['name'], 'sort_order' => $dirOrder],
            );

            foreach ($directorateData['divisions'] as $divOrder => $divisionData) {
                $division = Division::updateOrCreate(
                    ['code' => $divisionData['code']],
                    [
                        'directorate_id' => $directorate->id,
                        'name' => $divisionData['name'],
                        'type' => $divisionData['type'] ?? Division::TYPE_DIVISION,
                        'sort_order' => $divOrder,
                    ],
                );

                foreach ($divisionData['departments'] as $deptOrder => $departmentData) {
                    Department::updateOrCreate(
                        ['code' => $departmentData['code']],
                        [
                            'division_id' => $division->id,
                            'name' => $departmentData['name'],
                            'sort_order' => $deptOrder,
                        ],
                    );
                }
            }
        }
    }
}
