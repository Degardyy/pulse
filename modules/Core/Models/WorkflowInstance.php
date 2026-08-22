<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Models\Concerns\Auditable;

class WorkflowInstance extends Model
{
    use Auditable;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $table = 'core_workflow_instances';

    protected $fillable = ['definition_id', 'subject_type', 'subject_id', 'requested_by', 'status'];

    /** @return BelongsTo<WorkflowDefinition, $this> */
    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'definition_id');
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /** @return HasMany<WorkflowInstanceStep, $this> */
    public function instanceSteps(): HasMany
    {
        return $this->hasMany(WorkflowInstanceStep::class, 'instance_id')->orderBy('sort_order');
    }

    /** The step currently waiting for a decision (null once resolved). */
    public function currentStep(): ?WorkflowInstanceStep
    {
        return $this->instanceSteps->firstWhere('status', WorkflowInstanceStep::STATUS_PENDING);
    }

    public function subjectLabel(): string
    {
        $subject = $this->subject;

        return $subject?->title ?? $subject?->name ?? class_basename($this->subject_type)." #{$this->subject_id}";
    }
}
