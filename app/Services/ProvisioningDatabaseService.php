<?php

namespace App\Services;

use App\Contracts\Services\ProvisioningDatabaseServiceInterface;
use App\Models\ProvisioningDatabaseConnection;
use Illuminate\Support\Facades\DB;

class ProvisioningDatabaseService implements ProvisioningDatabaseServiceInterface
{
    private function configureConnection(ProvisioningDatabaseConnection $connection, ?string $database = null): void
    {
        config()->set('database.connections.provisioning_runtime', [
            'driver' => 'mysql',
            'host' => $connection->host,
            'port' => $connection->port,
            'database' => $database ?? 'information_schema',
            'username' => $connection->username,
            'password' => $connection->password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
        ]);
    }

    public function listDatabases(ProvisioningDatabaseConnection $connection): array
    {
        $this->configureConnection($connection);

        $rows = DB::connection('provisioning_runtime')->select('SHOW DATABASES');

        return array_map(static function ($item) {
            $values = array_values((array) $item);

            return $values[0] ?? null;
        }, $rows);
    }

    public function listTables(ProvisioningDatabaseConnection $connection, string $database): array
    {
        $this->configureConnection($connection, $database);

        $rows = DB::connection('provisioning_runtime')->select('SHOW TABLES');

        return array_map(static function ($item) {
            $values = array_values((array) $item);

            return $values[0] ?? null;
        }, $rows);
    }

    public function describeTable(ProvisioningDatabaseConnection $connection, string $database, string $table): array
    {
        $this->configureConnection($connection, $database);

        $columns = DB::connection('provisioning_runtime')->select("SHOW COLUMNS FROM `{$table}`");
        $indexes = DB::connection('provisioning_runtime')->select("SHOW INDEXES FROM `{$table}`");

        return [
            'columns' => $columns,
            'indexes' => $indexes,
        ];
    }
}
