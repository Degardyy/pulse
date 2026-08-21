<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Permission;
use Modules\Core\Models\Role;
use Modules\Core\Services\Access\PermissionSync;

/**
 * Built-in roles (ADR-007). Account administration authority sits with the
 * IT department (product-owner decision, 2026-08-21): grant IT staff the
 * "User Administrator" role; "Administrator" is the unrestricted system role
 * for platform administrators.
 */
class RbacSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionSync::class)->sync();

        Role::updateOrCreate(
            ['code' => Role::CODE_ADMINISTRATOR],
            [
                'name' => 'Administrator',
                'description' => 'Akses penuh seluruh PULSE (system role)',
                'is_system' => true,
                'is_super' => true,
            ],
        );

        $userAdmin = Role::updateOrCreate(
            ['code' => Role::CODE_USER_ADMINISTRATOR],
            [
                'name' => 'User Administrator',
                'description' => 'Mengelola akun pengguna — dipegang Department IT',
                'is_system' => true,
                'is_super' => false,
            ],
        );

        $userAdmin->permissions()->sync(
            Permission::whereIn('code', ['core.users.view', 'core.users.manage'])->pluck('id'),
        );
    }
}
