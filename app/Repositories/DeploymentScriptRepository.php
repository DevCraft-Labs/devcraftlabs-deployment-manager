<?php

namespace App\Repositories;

use App\Contracts\Repositories\DeploymentScriptRepositoryInterface;
use App\Models\DeploymentScript;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DeploymentScriptRepository implements DeploymentScriptRepositoryInterface
{
    public function paginate(?string $search = null, string $sort = 'created_at', string $direction = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        $sort = in_array($sort, ['name', 'created_at'], true) ? $sort : 'created_at';
        $direction = $direction === 'asc' ? 'asc' : 'desc';

        return DeploymentScript::query()
            ->with(['redisProfile', 'smtpProfile', 'telegramConnection'])
            ->when(filled($search), fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%')))
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();
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
