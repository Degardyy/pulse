<?php

namespace Modules\Core\Policies;

use Modules\Core\Models\Department;
use Modules\Core\Models\Document;
use Modules\Core\Models\User;

class DocumentPolicy
{
    /** Mirrors Document::scopeVisibleTo — keep the two in sync. */
    public function view(User $user, Document $document): bool
    {
        if ($document->uploaded_by === $user->id || $user->hasPermission('core.documents.manage')) {
            return true;
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
            Document::VISIBILITY_PALJAYA => $user->hasPermission('core.documents.publish-org'),
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
