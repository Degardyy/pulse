<?php

namespace Modules\Core\Events;

use Modules\Core\Models\WorkflowInstance;

class WorkflowApproved
{
    public function __construct(public readonly WorkflowInstance $instance) {}
}
