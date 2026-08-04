<?php

namespace App\Contracts\Repositories;

use App\Models\DeploymentExecution;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DeploymentExecutionRepositoryInterface
{
    public function create(array $data): DeploymentExecution;

    public function update(DeploymentExecution $execution, array $data): DeploymentExecution;

    public function latest(int $limit = 20): LengthAwarePaginator;
}
