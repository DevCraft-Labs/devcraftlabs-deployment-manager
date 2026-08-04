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
            'scripts.view',
            'scripts.create',
            'scripts.update',
            'scripts.delete',
            'connections.manage',
            'reports.export',
            'settings.manage',
            'api.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $admin = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions);

        $operator = Role::query()->firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        $operator->syncPermissions(['scripts.view', 'scripts.create', 'scripts.update', 'reports.export']);
    }
}
