<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStep extends Model
{
    public const APPROVER_POSITION = 'position';

    public const APPROVER_DEPARTMENT_HEAD = 'department_head';

    public const APPROVER_DIVISION_HEAD = 'division_head';

    public const APPROVER_ROLE = 'role';

    protected $table = 'core_workflow_steps';

    protected $fillable = ['definition_id', 'sort_order', 'name', 'approver_type', 'approver_value'];

    /** @return BelongsTo<WorkflowDefinition, $this> */
    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'definition_id');
    }
}
