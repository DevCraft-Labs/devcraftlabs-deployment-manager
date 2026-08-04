<?php

namespace Database\Seeders;

use App\Models\ApiToken;
use App\Models\ApplicationSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $admin = User::query()->updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'DevCraft Admin',
                'password' => Hash::make('admin12345'),
                'is_active' => true,
            ],
        );

        $admin->syncRoles(['Owner']);

        ApplicationSetting::query()->firstOrCreate([], [
            'server_name' => 'DevCraft Labs',
            'timezone' => 'UTC',
            'retention_days' => 30,
            'log_cleanup' => true,
        ]);

        ApiToken::query()->firstOrCreate(
            ['name' => 'default-api-token'],
            [
                'token_hash' => Hash::make('devcraft-api-token'),
                'user_id' => $admin->id,
                'is_active' => true,
            ],
        );
    }
}
