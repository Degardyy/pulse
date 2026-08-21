<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Services\Access\PermissionSync;

class SyncPermissionsCommand extends Command
{
    protected $signature = 'pulse:sync-permissions';

    protected $description = 'Mirror module-declared permissions into the database (run after deploying a module change)';

    public function handle(PermissionSync $sync): int
    {
        $result = $sync->sync();

        $this->info("Permissions synced: {$result['synced']}, pruned: {$result['pruned']}.");

        return self::SUCCESS;
    }
}
