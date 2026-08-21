<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Modules\Core\Services\OrganizationService;

class DashboardController extends Controller
{
    public function __construct(private readonly OrganizationService $organization) {}

    public function __invoke(): View
    {
        return view('core::dashboard', [
            'counts' => $this->organization->counts(),
        ]);
    }
}
