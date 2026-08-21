<?php

use Modules\Core\Providers\CoreServiceProvider;

/*
|--------------------------------------------------------------------------
| PULSE Modules
|--------------------------------------------------------------------------
| Explicit registry of active modules (ADR-002). Order matters: Core first,
| shared engines next, division portals last. Removing a line disables the
| module (routes, views, migrations) without deleting its code.
*/

return [

    'modules' => [
        CoreServiceProvider::class,
    ],

];
