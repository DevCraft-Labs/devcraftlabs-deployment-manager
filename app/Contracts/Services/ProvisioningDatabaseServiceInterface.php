<?php

namespace App\Contracts\Services;

use App\Models\ProvisioningDatabaseConnection;

interface ProvisioningDatabaseServiceInterface
{
    public function listDatabases(ProvisioningDatabaseConnection $connection): array;

    public function listTables(ProvisioningDatabaseConnection $connection, string $database): array;

    public function describeTable(ProvisioningDatabaseConnection $connection, string $database, string $table): array;
}
