<?php

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\Department;
use Modules\Core\Models\Document;
use Modules\Core\Models\User;
use Modules\Core\Services\Workflow\WorkflowService;

/**
 * Business logic for scoped documents. The service is the only path that
 * touches storage; controllers (and later the AI gateway) stay thin.
 */
class DocumentService
{
    public function __construct(private readonly Notifier $notifier) {}

    /**
     * @param  array{title: string, description?: ?string, category?: ?string, visibility: string, division_id?: ?int, department_id?: ?int}  $data
     */
    public function store(User $uploader, array $data, UploadedFile $file): Document
    {
        // Org-wide publication without the direct-publish permission goes
        // through the approval workflow (ADR-009) instead of going live.
        $needsApproval = $data['visibility'] === Document::VISIBILITY_PALJAYA
            && ! $uploader->hasPermission('core.documents.publish-org')
            && ! $uploader->hasPermission('core.documents.manage');

        $document = Document::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
            'file_path' => $file->store('documents'),
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize() ?: 0,
            'visibility' => $data['visibility'],
            'status' => $needsApproval ? Document::STATUS_PENDING_APPROVAL : Document::STATUS_PUBLISHED,
            'division_id' => $data['visibility'] === Document::VISIBILITY_DIVISION ? $data['division_id'] : null,
            'department_id' => $data['visibility'] === Document::VISIBILITY_DEPARTMENT ? $data['department_id'] : null,
            'uploaded_by' => $uploader->id,
        ]);

        if ($needsApproval) {
            app(WorkflowService::class)->start(self::WORKFLOW_PUBLISH_ORG, $document, $uploader);
        } else {
            $this->notifyAudience($document);
        }

        return $document;
    }

    /** Workflow definition code for org-wide publication approval. */
    public const WORKFLOW_PUBLISH_ORG = 'document.publish-org';

    /** Called when the publish-org workflow approves the document. */
    public function publishApproved(Document $document): void
    {
        $document->update(['status' => Document::STATUS_PUBLISHED]);
        $this->notifyAudience($document);
    }

    /** Called when the publish-org workflow rejects the document. */
    public function markRejected(Document $document): void
    {
        $document->update(['status' => Document::STATUS_REJECTED]);
    }

    private function notifyAudience(Document $document): void
    {
        $audience = $this->audienceFor($document)
            ->where('id', '!=', $document->uploaded_by)
            ->get();

        if ($audience->isNotEmpty()) {
            $this->notifier->send(
                $audience,
                'Dokumen baru dibagikan',
                "\"{$document->title}\" — {$document->visibilityLabel()}",
                route('core.documents.index'),
            );
        }
    }

    public function delete(Document $document): void
    {
        Storage::delete($document->file_path);
        $document->delete();
    }

    /**
     * Active users who can see the document by membership (notification audience).
     *
     * @return Builder<User>
     */
    private function audienceFor(Document $document)
    {
        $query = User::query()->where('is_active', true);

        if ($document->visibility === Document::VISIBILITY_PALJAYA) {
            return $query;
        }

        if ($document->visibility === Document::VISIBILITY_DIVISION) {
            $divisionId = $document->division_id;

            return $query->whereHas('employee.activeAssignments.position', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId)
                    ->orWhereHas('department', fn ($q) => $q->where('division_id', $divisionId));
            });
        }

        $department = Department::find($document->department_id);

        return $query->whereHas('employee.activeAssignments.position', function ($q) use ($department) {
            $q->where('department_id', $department?->id)
                ->orWhere('division_id', $department?->division_id); // division leadership
        });
    }
}
