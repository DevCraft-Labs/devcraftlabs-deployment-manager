<?php

namespace App\Repositories;

use App\Contracts\Repositories\DeploymentExecutionRepositoryInterface;
use App\Models\DeploymentExecution;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DeploymentExecutionRepository implements DeploymentExecutionRepositoryInterface
{
    public function create(array $data): DeploymentExecution
    {
        return DeploymentExecution::query()->create($data);
    }

    public function update(DeploymentExecution $execution, array $data): DeploymentExecution
    {
        $execution->update($data);

        return $execution->refresh();
    }

    public function latest(int $limit = 20): LengthAwarePaginator
    {
        return DeploymentExecution::query()
            ->with(['script', 'user'])
            ->latest('started_at')
            ->paginate($limit);
    }
}
