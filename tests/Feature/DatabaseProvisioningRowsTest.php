<?php

namespace Tests\Feature;

use App\Contracts\Services\ProvisioningDatabaseServiceInterface;
use App\Models\ProvisioningDatabaseConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DatabaseProvisioningRowsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_row_in_a_selected_table(): void
    {
        $connection = $this->connection();
        $service = $this->serviceMock();
        $service->shouldReceive('insertRow')->once()->withArgs(function ($receivedConnection, $database, $table, $description, $data) use ($connection) {
            return $receivedConnection->is($connection)
                && $database === 'application'
                && $table === 'items'
                && $description['columns'][0]->Field === 'id'
                && $description['indexes'][0]->Key_name === 'PRIMARY'
                && $data === ['name' => 'New item'];
        });

        $this->actingAs($this->owner())->post(route('provisioning.rows.store', $connection), [
            'database' => 'application',
            'table' => 'items',
            'data' => ['name' => 'New item'],
        ])->assertRedirect(route('provisioning.browse', ['connection' => $connection, 'database' => 'application', 'table' => 'items']));

        $this->assertDatabaseHas('activity_audits', ['action' => 'db.provisioning.data.create']);
    }

    public function test_owner_can_update_and_delete_a_row_by_its_primary_key(): void
    {
        $connection = $this->connection();
        $key = base64_encode(json_encode(['id' => 7]));
        $service = $this->serviceMock();
        $service->shouldReceive('updateRow')->once()->andReturnTrue();
        $service->shouldReceive('deleteRow')->once()->andReturnTrue();

        $this->actingAs($this->owner())->put(route('provisioning.rows.update', $connection), [
            'database' => 'application',
            'table' => 'items',
            'key' => $key,
            'data' => ['name' => 'Updated item'],
        ])->assertRedirect();

        $this->delete(route('provisioning.rows.destroy', $connection), [
            'database' => 'application',
            'table' => 'items',
            'key' => $key,
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_audits', ['action' => 'db.provisioning.data.update']);
        $this->assertDatabaseHas('activity_audits', ['action' => 'db.provisioning.data.delete']);
    }

    private function serviceMock(): \Mockery\MockInterface
    {
        $service = Mockery::mock(ProvisioningDatabaseServiceInterface::class);
        $service->shouldReceive('listDatabases')->zeroOrMoreTimes()->andReturn(['application']);
        $service->shouldReceive('listTables')->zeroOrMoreTimes()->andReturn(['items']);
        $service->shouldReceive('describeTable')->zeroOrMoreTimes()->andReturn($this->description());
        $this->app->instance(ProvisioningDatabaseServiceInterface::class, $service);

        return $service;
    }

    private function description(): array
    {
        return [
            'columns' => [(object) ['Field' => 'id', 'Extra' => 'auto_increment', 'Null' => 'NO', 'Default' => null], (object) ['Field' => 'name', 'Extra' => '', 'Null' => 'NO', 'Default' => null]],
            'indexes' => [(object) ['Key_name' => 'PRIMARY', 'Seq_in_index' => 1, 'Column_name' => 'id']],
        ];
    }

    private function owner(): User
    {
        $role = Role::findOrCreate('Owner');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function connection(): ProvisioningDatabaseConnection
    {
        return ProvisioningDatabaseConnection::query()->create([
            'name' => 'Test MySQL',
            'host' => '127.0.0.1',
            'port' => 3306,
            'username' => 'test',
            'password' => 'test',
            'status' => true,
        ]);
    }
}