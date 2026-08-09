<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view',
            'users.manage',
            'scripts.view',
            'scripts.create',
            'scripts.update',
            'scripts.delete',
            'scripts.run',
            'connections.view',
            'connections.create',
            'connections.update',
            'connections.delete',
            'connections.test',
            'deployments.view',
            'cron.view',
            'cron.update',
            'provisioning.view',
            'provisioning.create',
            'provisioning.update',
            'provisioning.delete',
            'reports.export',
            'settings.view',
            'settings.manage',
            'api.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $owner = Role::query()->firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);
        $owner->syncPermissions($permissions);

        $developer = Role::query()->firstOrCreate(['name' => 'Developer', 'guard_name' => 'web']);
        $developer->syncPermissions([
            'dashboard.view',
            'scripts.view',
            'scripts.create',
            'scripts.update',
            'scripts.run',
            'connections.view',
            'connections.create',
            'connections.update',
            'connections.test',
            'deployments.view',
            'cron.view',
            'cron.update',
            'provisioning.view',
            'provisioning.create',
            'provisioning.update',
            'reports.export',
            'settings.view',
        ]);

        $viewer = Role::query()->firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions([
            'dashboard.view',
            'scripts.view',
            'connections.view',
            'deployments.view',
            'cron.view',
            'provisioning.view',
            'settings.view',
        ]);

        Role::query()->whereIn('name', ['admin', 'operator'])->delete();

        // The file/text clipboard is a public, unauthenticated feature and
        // never had a matching Gate check; drop the permission rows a prior
        // version of this seeder created for it.
        Permission::query()->where('name', 'like', 'clipboard.%')->delete();
    }
}
