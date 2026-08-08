<?php

namespace App\Services;

use App\Contracts\Services\ProvisioningDatabaseServiceInterface;
use App\Models\ProvisioningDatabaseConnection;
use Illuminate\Support\Facades\DB;

class ProvisioningDatabaseService implements ProvisioningDatabaseServiceInterface
{
    /**
     * MySQL system/metadata schemas that are never business data.
     */
    private const SYSTEM_SCHEMAS = [
        'information_schema',
        'mysql',
        'performance_schema',
        'sys',
    ];

    private function identifier(string $value): string
    {
        if (!preg_match('/^[A-Za-z0-9$_]+$/', $value)) {
            throw new \InvalidArgumentException('Invalid database identifier.');
        }

        return sprintf('`%s`', $value);
    }

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

        // Laravel caches resolved PDO connections by name, so a stale connection
        // bound to the previous database would otherwise survive a config change.
        DB::purge('provisioning_runtime');
    }

    public function listDatabases(ProvisioningDatabaseConnection $connection): array
    {
        $this->configureConnection($connection);

        $rows = DB::connection('provisioning_runtime')->select('SHOW DATABASES');

        $databases = array_map(static function ($item) {
            $values = array_values((array) $item);

            return $values[0] ?? null;
        }, $rows);

        return array_values(array_filter($databases, static function (?string $database) {
            return $database !== null && !in_array(strtolower($database), self::SYSTEM_SCHEMAS, true);
        }));
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
        $table = $this->identifier($table);

        $columns = DB::connection('provisioning_runtime')->select("SHOW COLUMNS FROM {$table}");
        $indexes = DB::connection('provisioning_runtime')->select("SHOW INDEXES FROM {$table}");

        return [
            'columns' => $columns,
            'indexes' => $indexes,
        ];
    }
}
