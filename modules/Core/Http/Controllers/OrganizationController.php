<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Modules\Core\Services\OrganizationService;

class OrganizationController extends Controller
{
    public function __construct(private readonly OrganizationService $organization) {}

    public function index(): View
    {
        return view('core::organization.index', [
            'directorates' => $this->organization->structureTree(),
            'counts' => $this->organization->counts(),
        ]);
    }
}
