<?php

namespace App\Contracts\Services;

use App\Models\ProvisioningDatabaseConnection;

interface ProvisioningDatabaseServiceInterface
{
    public function listDatabases(ProvisioningDatabaseConnection $connection): array;

    public function listTables(ProvisioningDatabaseConnection $connection, string $database): array;

    public function describeTable(ProvisioningDatabaseConnection $connection, string $database, string $table): array;

    public function tableRows(ProvisioningDatabaseConnection $connection, string $database, string $table, array $description): \Illuminate\Pagination\LengthAwarePaginator;

    public function insertRow(ProvisioningDatabaseConnection $connection, string $database, string $table, array $description, array $data): void;

    public function updateRow(ProvisioningDatabaseConnection $connection, string $database, string $table, array $description, array $key, array $data): bool;

    public function deleteRow(ProvisioningDatabaseConnection $connection, string $database, string $table, array $description, array $key): bool;
}
