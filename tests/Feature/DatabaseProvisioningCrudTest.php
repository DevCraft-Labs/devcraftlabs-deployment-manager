<?php

namespace Tests\Feature;

use App\Models\ProvisioningDatabaseConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DatabaseProvisioningCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_update_and_delete_a_provisioning_connection(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->post(route('provisioning.store'), [
            'name' => 'Primary MySQL',
            'host' => '127.0.0.1',
            'port' => 3306,
            'username' => 'sysadmin',
            'password' => 'admin12345',
            'status' => '1',
        ])->assertRedirect(route('provisioning.index'));

        $connection = ProvisioningDatabaseConnection::query()->firstOrFail();
        $originalPassword = $connection->password;

        $this->assertDatabaseHas('provisioning_database_connections', [
            'id' => $connection->id,
            'name' => 'Primary MySQL',
            'host' => '127.0.0.1',
            'port' => 3306,
            'username' => 'sysadmin',
        ]);

        $this->actingAs($owner)->put(route('provisioning.update', $connection), [
            'name' => 'Primary MySQL Updated',
            'host' => 'localhost',
            'port' => 3307,
            'username' => 'deploy',
            'password' => '',
            'status' => '0',
        ])->assertRedirect(route('provisioning.index'));

        $connection->refresh();
        $this->assertSame($originalPassword, $connection->password);
        $this->assertSame('Primary MySQL Updated', $connection->name);
        $this->assertFalse($connection->status);

        $this->actingAs($owner)
            ->delete(route('provisioning.destroy', $connection))
            ->assertRedirect(route('provisioning.index'));

        $this->assertDatabaseMissing('provisioning_database_connections', ['id' => $connection->id]);
        $this->assertDatabaseHas('activity_audits', ['action' => 'db.provisioning.delete']);
    }

    private function owner(): User
    {
        $role = Role::findOrCreate('Owner');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}