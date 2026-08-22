<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Models\WorkflowInstance;
use Modules\Core\Services\Workflow\WorkflowService;

class ApprovalController extends Controller
{
    public function __construct(private readonly WorkflowService $workflow) {}

    public function index(Request $request): View
    {
        return view('core::approvals.index', [
            'pending' => $this->workflow->pendingFor($request->user()),
            'requests' => $this->workflow->requestsOf($request->user()),
        ]);
    }

    public function approve(Request $request, WorkflowInstance $instance): RedirectResponse
    {
        $request->validate(['note' => ['nullable', 'string', 'max:500']]);

        // Eligibility is enforced inside the engine (403 otherwise).
        $this->workflow->approve($instance, $request->user(), $request->input('note'));

        return redirect()->route('core.approvals.index')
            ->with('status', "\"{$instance->subjectLabel()}\" disetujui.");
    }

    public function reject(Request $request, WorkflowInstance $instance): RedirectResponse
    {
        $request->validate(['note' => ['nullable', 'string', 'max:500']]);

        $this->workflow->reject($instance, $request->user(), $request->input('note'));

        return redirect()->route('core.approvals.index')
            ->with('status', "\"{$instance->subjectLabel()}\" ditolak.");
    }
}
