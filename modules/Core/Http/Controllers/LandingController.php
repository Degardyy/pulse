<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class LandingController extends Controller
{
    public function __invoke(): View
    {
        return view('core::landing', [
            'stage' => 'Foundation',
            'modules' => array_map(
                fn (string $provider) => explode('\\', $provider)[1] ?? $provider,
                config('modules.modules', []),
            ),
        ]);
    }
}
