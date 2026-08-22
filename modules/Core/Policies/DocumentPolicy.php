<?php

namespace Modules\Core\Policies;

use Modules\Core\Models\Department;
use Modules\Core\Models\Document;
use Modules\Core\Models\User;
use Modules\Core\Services\Workflow\WorkflowService;

class DocumentPolicy
{
    /** Mirrors Document::scopeVisibleTo — keep the two in sync. */
    public function view(User $user, Document $document): bool
    {
        if ($document->uploaded_by === $user->id || $user->hasPermission('core.documents.manage')) {
            return true;
        }

        // Awaiting/denied publication: readable only by uploader (above),
        // managers (above), and the current workflow approver (must review it).
        if ($document->status !== Document::STATUS_PUBLISHED) {
            return $document->status === Document::STATUS_PENDING_APPROVAL
                && app(WorkflowService::class)->isApproverFor($user, $document);
        }

        $units = $user->organizationUnitIds();

        return match ($document->visibility) {
            Document::VISIBILITY_PALJAYA => true,
            Document::VISIBILITY_DIVISION => in_array($document->division_id, $units['divisions'], true),
            Document::VISIBILITY_DEPARTMENT => in_array($document->department_id, $units['departments'], true)
                || ($units['division_leads'] !== []
                    && in_array($document->department?->division_id, $units['division_leads'], true)),
            default => false,
        };
    }

    /** Can the user publish anything at all (gates the upload page). */
    public function create(User $user): bool
    {
        $units = $user->organizationUnitIds();

        return $units['departments'] !== [] || $units['divisions'] !== []
            || $user->hasPermission('core.documents.publish-org')
            || $user->hasPermission('core.documents.manage');
    }

    /** Target-scope check used by the store request. */
    public function createWithScope(User $user, string $visibility, ?int $divisionId, ?int $departmentId): bool
    {
        if ($user->hasPermission('core.documents.manage')) {
            return true;
        }

        $units = $user->organizationUnitIds();

        return match ($visibility) {
            // Anyone with an org unit may REQUEST org-wide publication (it
            // then goes through approval, ADR-009); the publish-org permission
            // publishes directly.
            Document::VISIBILITY_PALJAYA => $user->hasPermission('core.documents.publish-org')
                || $units['departments'] !== [] || $units['divisions'] !== [],
            Document::VISIBILITY_DIVISION => in_array($divisionId, $units['divisions'], true),
            Document::VISIBILITY_DEPARTMENT => in_array($departmentId, $units['departments'], true)
                || ($departmentId !== null
                    && in_array(Department::find($departmentId)?->division_id, $units['division_leads'], true)),
            default => false,
        };
    }

    public function delete(User $user, Document $document): bool
    {
        return $document->uploaded_by === $user->id || $user->hasPermission('core.documents.manage');
    }
}
