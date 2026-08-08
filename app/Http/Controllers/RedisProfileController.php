<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\RedisProfileRepositoryInterface;
use App\Contracts\Services\RedisConnectionServiceInterface;
use App\Http\Requests\RedisProfile\StoreRedisProfileRequest;
use App\Http\Requests\RedisProfile\UpdateRedisProfileRequest;
use App\Models\RedisProfile;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RedisProfileController extends Controller
{
    public function __construct(
        private readonly RedisProfileRepositoryInterface $repository,
        private readonly RedisConnectionServiceInterface $redisConnectionService,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('redis.index', ['profiles' => $this->repository->paginate()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('redis.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRedisProfileRequest $request): RedirectResponse
    {
        $profile = $this->repository->create($request->validated());
        $this->auditLogger->log('redis.create', RedisProfile::class, $profile->id);

        return redirect()->route('redis-profiles.index')->with('status', 'Redis profile created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(RedisProfile $redisProfile): View
    {
        return view('redis.show', ['profile' => $redisProfile]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RedisProfile $redisProfile): View
    {
        return view('redis.edit', ['profile' => $redisProfile]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRedisProfileRequest $request, RedisProfile $redisProfile): RedirectResponse
    {
        $this->repository->update($redisProfile, $request->validated());
        $this->auditLogger->log('redis.update', RedisProfile::class, $redisProfile->id);

        return redirect()->route('redis-profiles.index')->with('status', 'Redis profile updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RedisProfile $redisProfile): RedirectResponse
    {
        $this->repository->delete($redisProfile);
        $this->auditLogger->log('redis.delete', RedisProfile::class, $redisProfile->id);

        return redirect()->route('redis-profiles.index')->with('status', 'Redis profile deleted.');
    }

    public function test(Request $request, RedisProfile $redisProfile): RedirectResponse
    {
        $ttl = (int) $request->integer('ttl', $redisProfile->default_ttl);
        $result = $this->redisConnectionService->testConnection($redisProfile, Auth::user(), $ttl);
        $this->auditLogger->log('redis.test', RedisProfile::class, $redisProfile->id, ['success' => $result->success]);

        return back()->with('status', $result->response);
    }

    /**
     * Display the key browser for the given Redis profile.
     */
    public function keys(RedisProfile $redisProfile): View
    {
        return view('redis.keys', ['profile' => $redisProfile]);
    }

    /**
     * Search/scan keys for the given Redis profile (AJAX).
     */
    public function keysData(Request $request, RedisProfile $redisProfile): JsonResponse
    {
        $search = $request->query('search');
        $result = $this->redisConnectionService->scanKeys($redisProfile, $search !== '' ? $search : null);

        return response()->json($result);
    }

    /**
     * Fetch a single key's type, TTL, and value (AJAX).
     */
    public function keyShow(Request $request, RedisProfile $redisProfile): JsonResponse
    {
        $request->validate(['key' => ['required', 'string']]);

        try {
            $details = $this->redisConnectionService->getKeyDetails($redisProfile, $request->string('key')->toString());

            return response()->json($details);
        } catch (\Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    /**
     * Update a key's value (and optionally TTL) (AJAX).
     */
    public function keyUpdate(Request $request, RedisProfile $redisProfile): JsonResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string'],
            'type' => ['required', 'string', 'in:string,hash,list,set,zset'],
            'value' => ['required'],
            'ttl' => ['nullable', 'integer', 'min:1'],
        ]);

        try {
            $this->redisConnectionService->updateKeyValue(
                $redisProfile,
                $validated['key'],
                $validated['type'],
                $validated['value'],
                $validated['ttl'] ?? null,
            );
            $this->auditLogger->log('redis.key.update', RedisProfile::class, $redisProfile->id, ['key' => $validated['key']]);

            return response()->json(['status' => 'ok']);
        } catch (\Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Delete a key (AJAX).
     */
    public function keyDestroy(Request $request, RedisProfile $redisProfile): JsonResponse
    {
        $request->validate(['key' => ['required', 'string']]);
        $key = $request->string('key')->toString();

        $this->redisConnectionService->deleteKey($redisProfile, $key);
        $this->auditLogger->log('redis.key.delete', RedisProfile::class, $redisProfile->id, ['key' => $key]);

        return response()->json(['status' => 'ok']);
    }
}
