<?php

namespace App\Services;

use App\Contracts\Services\ProvisioningDatabaseServiceInterface;
use App\Models\ProvisioningDatabaseConnection;
use Illuminate\Pagination\LengthAwarePaginator;
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

    public function tableRows(ProvisioningDatabaseConnection $connection, string $database, string $table, array $description): LengthAwarePaginator
    {
        $this->configureConnection($connection, $database);
        $columns = $this->columnNames($description);
        $orderBy = $this->primaryKeyColumns($description)[0] ?? $columns[0] ?? null;

        if ($orderBy === null) {
            throw new \InvalidArgumentException('The selected table has no columns.');
        }

        return DB::connection('provisioning_runtime')->table(DB::raw($this->identifier($table)))->orderBy($orderBy)->paginate(25);
    }

    public function insertRow(ProvisioningDatabaseConnection $connection, string $database, string $table, array $description, array $data): void
    {
        $this->configureConnection($connection, $database);
        $data = $this->writableData($description, $data);

        if ($data === []) {
            throw new \InvalidArgumentException('Provide at least one writable column value.');
        }

        DB::connection('provisioning_runtime')->table(DB::raw($this->identifier($table)))->insert($data);
    }

    public function updateRow(ProvisioningDatabaseConnection $connection, string $database, string $table, array $description, array $key, array $data): bool
    {
        $this->configureConnection($connection, $database);
        $data = $this->writableData($description, $data);

        if ($data === []) {
            throw new \InvalidArgumentException('Provide at least one writable column value.');
        }

        $query = DB::connection('provisioning_runtime')
            ->table(DB::raw($this->identifier($table)))
            ->where($this->primaryKey($description, $key));

        if (!$query->exists()) {
            return false;
        }

        $query->update($data);

        return true;
    }

    public function deleteRow(ProvisioningDatabaseConnection $connection, string $database, string $table, array $description, array $key): bool
    {
        $this->configureConnection($connection, $database);

        return DB::connection('provisioning_runtime')
            ->table(DB::raw($this->identifier($table)))
            ->where($this->primaryKey($description, $key))
            ->delete() > 0;
    }

    private function columnNames(array $description): array
    {
        return array_map(fn (object $column) => trim($column->Field), $description['columns']);
    }

    private function writableData(array $description, array $data): array
    {
        $columns = [];

        foreach ($description['columns'] as $column) {
            if (str_contains(strtolower($column->Extra), 'auto_increment') || str_contains(strtolower($column->Extra), 'generated')) {
                continue;
            }

            $columns[$column->Field] = $column;
        }

        $data = array_intersect_key($data, $columns);

        foreach ($data as $name => $value) {
            if (!is_scalar($value) && $value !== null) {
                throw new \InvalidArgumentException("Invalid value supplied for {$name}.");
            }

            if ($value === '' && $columns[$name]->Null === 'YES') {
                $data[$name] = null;
            }
        }

        return $data;
    }

    private function primaryKeyColumns(array $description): array
    {
        $columns = [];

        foreach ($description['indexes'] as $index) {
            if ($index->Key_name === 'PRIMARY') {
                $columns[(int) $index->Seq_in_index] = $index->Column_name;
            }
        }

        ksort($columns);

        return array_values($columns);
    }

    private function primaryKey(array $description, array $key): array
    {
        $columns = $this->primaryKeyColumns($description);

        if ($columns === [] || array_diff($columns, array_keys($key)) !== []) {
            throw new \InvalidArgumentException('A complete primary key is required to modify a row.');
        }

        return array_intersect_key($key, array_flip($columns));
    }
}
