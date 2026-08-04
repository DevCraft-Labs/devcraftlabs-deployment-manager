<?php

namespace App\Contracts\Repositories;

use App\Models\RedisProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RedisProfileRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): RedisProfile;

    public function create(array $data): RedisProfile;

    public function update(RedisProfile $profile, array $data): RedisProfile;

    public function delete(RedisProfile $profile): void;
}
