<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\DeploymentScriptRepositoryInterface;
use App\Contracts\Services\DeploymentServiceInterface;
use App\Http\Requests\DeploymentScript\StoreDeploymentScriptRequest;
use App\Http\Requests\DeploymentScript\UpdateDeploymentScriptRequest;
use App\Models\DeploymentScript;
use App\Models\RedisProfile;
use App\Models\SmtpProfile;
use App\Models\TelegramConnection;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DeploymentScriptController extends Controller
{
    public function __construct(
        private readonly DeploymentScriptRepositoryInterface $repository,
        private readonly DeploymentServiceInterface $deploymentService,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('scripts.index', ['scripts' => $this->repository->paginate()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('scripts.create', [
            'redisProfiles' => RedisProfile::query()->where('status', true)->orderBy('name')->get(),
            'smtpProfiles' => SmtpProfile::query()->where('status', true)->orderBy('name')->get(),
            'telegramConnections' => TelegramConnection::query()->where('status', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDeploymentScriptRequest $request): RedirectResponse
    {
        $script = $this->repository->create($request->validated());
        $this->auditLogger->log('script.create', DeploymentScript::class, $script->id);

        return redirect()->route('deployment-scripts.index')->with('status', 'Script created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DeploymentScript $deploymentScript): View
    {
        return view('scripts.show', ['script' => $deploymentScript->load(['executions' => fn ($q) => $q->latest('started_at')->limit(20)])]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DeploymentScript $deploymentScript): View
    {
        return view('scripts.edit', [
            'script' => $deploymentScript,
            'redisProfiles' => RedisProfile::query()->where('status', true)->orderBy('name')->get(),
            'smtpProfiles' => SmtpProfile::query()->where('status', true)->orderBy('name')->get(),
            'telegramConnections' => TelegramConnection::query()->where('status', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDeploymentScriptRequest $request, DeploymentScript $deploymentScript): RedirectResponse
    {
        $this->repository->update($deploymentScript, $request->validated());
        $this->auditLogger->log('script.update', DeploymentScript::class, $deploymentScript->id);

        return redirect()->route('deployment-scripts.index')->with('status', 'Script updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeploymentScript $deploymentScript): RedirectResponse
    {
        $this->repository->delete($deploymentScript);
        $this->auditLogger->log('script.delete', DeploymentScript::class, $deploymentScript->id);

        return redirect()->route('deployment-scripts.index')->with('status', 'Script deleted successfully.');
    }

    public function run(DeploymentScript $deploymentScript): RedirectResponse
    {
        $this->deploymentService->queue($deploymentScript, Auth::user(), 'manual');
        $this->auditLogger->log('script.run', DeploymentScript::class, $deploymentScript->id);

        return back()->with('status', 'Script execution queued.');
    }

    public function duplicate(DeploymentScript $deploymentScript): RedirectResponse
    {
        $copy = $deploymentScript->replicate();
        $copy->name = $deploymentScript->name . '-' . Str::random(5);
        $copy->save();

        $this->auditLogger->log('script.duplicate', DeploymentScript::class, $copy->id);

        return redirect()->route('deployment-scripts.edit', $copy)->with('status', 'Script duplicated.');
    }

    public function toggle(DeploymentScript $deploymentScript): RedirectResponse
    {
        $deploymentScript->update(['active' => !$deploymentScript->active]);
        $this->auditLogger->log('script.toggle', DeploymentScript::class, $deploymentScript->id, ['active' => $deploymentScript->active]);

        return back()->with('status', 'Script state updated.');
    }
}
