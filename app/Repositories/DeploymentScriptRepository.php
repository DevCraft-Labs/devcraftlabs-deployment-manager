<?php

namespace App\Repositories;

use App\Contracts\Repositories\DeploymentScriptRepositoryInterface;
use App\Models\DeploymentScript;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DeploymentScriptRepository implements DeploymentScriptRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return DeploymentScript::query()
            ->with(['redisProfile', 'smtpProfile', 'telegramConnection'])
            ->latest()
            ->paginate($perPage);
    }

    public function findOrFail(int $id): DeploymentScript
    {
        return DeploymentScript::query()->with(['redisProfile', 'smtpProfile', 'telegramConnection'])->findOrFail($id);
    }

    public function create(array $data): DeploymentScript
    {
        return DeploymentScript::query()->create($data);
    }

    public function update(DeploymentScript $script, array $data): DeploymentScript
    {
        $script->update($data);

        return $script->refresh();
    }

    public function delete(DeploymentScript $script): void
    {
        $script->delete();
    }
}
