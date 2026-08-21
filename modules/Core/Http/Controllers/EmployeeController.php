<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Modules\Core\Services\EmployeeService;

class EmployeeController extends Controller
{
    public function __construct(private readonly EmployeeService $employees) {}

    public function index(): View
    {
        return view('core::employees.index', [
            'employees' => $this->employees->listWithPositions(),
            'counts' => $this->employees->counts(),
        ]);
    }
}
