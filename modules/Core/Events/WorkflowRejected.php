<?php

namespace Modules\Core\Events;

use Modules\Core\Models\WorkflowInstance;

class WorkflowRejected
{
    public function __construct(
        public readonly WorkflowInstance $instance,
        public readonly ?string $note = null,
    ) {}
}
