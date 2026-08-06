<?php

namespace App\Jobs;

use App\Contracts\Services\DeploymentServiceInterface;
use App\Models\DeploymentExecution;
use App\Models\DeploymentScript;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class RunDeploymentScriptJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly int $scriptId,
        private readonly ?int $userId,
        private readonly string $triggeredVia,
        private readonly int $executionId,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(DeploymentServiceInterface $deploymentService): void
    {
        $script = DeploymentScript::query()->findOrFail($this->scriptId);
        $user = $this->userId ? User::query()->find($this->userId) : null;
        $execution = DeploymentExecution::query()->findOrFail($this->executionId);

        $deploymentService->execute($script, $user, $this->triggeredVia, $execution);
    }

    public function failed(Throwable $exception): void
    {
        DeploymentExecution::query()->whereKey($this->executionId)->update([
            'status' => 'failed',
            'is_success' => false,
            'finished_at' => now(),
            'stderr' => $exception->getMessage(),
            'failure_report' => "DEPLOYMENT FAILURE REPORT\nExecution ID: {$this->executionId}\n\nFailure reason:\n{$exception->getMessage()}",
        ]);
    }
}
