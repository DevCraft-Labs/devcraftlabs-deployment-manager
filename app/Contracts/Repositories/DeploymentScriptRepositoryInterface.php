<?php

namespace App\Contracts\Repositories;

use App\Models\DeploymentScript;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DeploymentScriptRepositoryInterface
{
    public function paginate(?string $search = null, string $sort = 'created_at', string $direction = 'desc', int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): DeploymentScript;

    public function create(array $data): DeploymentScript;

    public function update(DeploymentScript $script, array $data): DeploymentScript;

    public function delete(DeploymentScript $script): void;
}
