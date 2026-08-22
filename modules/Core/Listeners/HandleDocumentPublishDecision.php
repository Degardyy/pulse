<?php

namespace Modules\Core\Listeners;

use Modules\Core\Events\WorkflowApproved;
use Modules\Core\Events\WorkflowRejected;
use Modules\Core\Models\Document;
use Modules\Core\Services\DocumentService;

/**
 * Reacts to the outcome of the org-wide document publication workflow.
 * The engine stays generic; this listener is the document-specific glue.
 */
class HandleDocumentPublishDecision
{
    public function __construct(private readonly DocumentService $documents) {}

    public function handleApproved(WorkflowApproved $event): void
    {
        if ($this->isPublishOrg($event->instance->definition->code)
            && ($document = $event->instance->subject) instanceof Document) {
            $this->documents->publishApproved($document);
        }
    }

    public function handleRejected(WorkflowRejected $event): void
    {
        if ($this->isPublishOrg($event->instance->definition->code)
            && ($document = $event->instance->subject) instanceof Document) {
            $this->documents->markRejected($document);
        }
    }

    private function isPublishOrg(string $code): bool
    {
        return $code === DocumentService::WORKFLOW_PUBLISH_ORG;
    }
}
