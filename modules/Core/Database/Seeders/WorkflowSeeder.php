<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\WorkflowDefinition;
use Modules\Core\Models\WorkflowStep;
use Modules\Core\Services\DocumentService;

/**
 * Built-in workflow definitions (ADR-009).
 *
 * ASUMSI (menunggu SOP resmi — open question #4): publikasi dokumen ke
 * seluruh Paljaya disetujui oleh Division Head Corporate Secretary.
 */
class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $definition = WorkflowDefinition::updateOrCreate(
            ['code' => DocumentService::WORKFLOW_PUBLISH_ORG],
            [
                'name' => 'Publikasi Dokumen Seluruh Paljaya',
                'description' => 'Persetujuan sebelum dokumen dapat dibaca seluruh organisasi',
                'is_active' => true,
            ],
        );

        WorkflowStep::updateOrCreate(
            ['definition_id' => $definition->id, 'sort_order' => 1],
            [
                'name' => 'Persetujuan Corporate Secretary',
                'approver_type' => WorkflowStep::APPROVER_POSITION,
                'approver_value' => 'DIVH-CSEC',
            ],
        );
    }
}
