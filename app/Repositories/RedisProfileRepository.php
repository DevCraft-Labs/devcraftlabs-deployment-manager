<?php

namespace App\Repositories;

use App\Contracts\Repositories\RedisProfileRepositoryInterface;
use App\Models\RedisProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RedisProfileRepository implements RedisProfileRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return RedisProfile::query()->latest()->paginate($perPage);
    }

    public function findOrFail(int $id): RedisProfile
    {
        return RedisProfile::query()->findOrFail($id);
    }

    public function create(array $data): RedisProfile
    {
        return RedisProfile::query()->create($data);
    }

    public function update(RedisProfile $profile, array $data): RedisProfile
    {
        $profile->update($data);

        return $profile->refresh();
    }

    public function delete(RedisProfile $profile): void
    {
        $profile->delete();
    }
}
