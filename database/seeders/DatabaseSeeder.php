<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Database\Seeders\OfficialsSeeder;
use Modules\Core\Database\Seeders\OrganizationSeeder;
use Modules\Core\Database\Seeders\RbacSeeder;
use Modules\Core\Models\Role;
use Modules\Core\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(OrganizationSeeder::class);
        $this->call(OfficialsSeeder::class);
        $this->call(RbacSeeder::class);

        // Initial administrator account. Override via env; ALWAYS change the
        // password after first login on any non-local environment.
        $admin = User::updateOrCreate(
            ['email' => env('PULSE_ADMIN_EMAIL', 'admin@paljaya.local')],
            [
                'name' => 'PULSE Administrator',
                'password' => env('PULSE_ADMIN_PASSWORD', 'password'),
                'auth_provider' => User::PROVIDER_LOCAL,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $adminRole = Role::where('code', Role::CODE_ADMINISTRATOR)->firstOrFail();

        if (! $admin->roles()->where('core_roles.id', $adminRole->id)->exists()) {
            $admin->roles()->attach($adminRole->id);
        }
    }
}
