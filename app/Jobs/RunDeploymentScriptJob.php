<?php

namespace App\Jobs;

use App\Contracts\Services\DeploymentServiceInterface;
use App\Models\DeploymentScript;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(DeploymentServiceInterface $deploymentService): void
    {
        $script = DeploymentScript::query()->findOrFail($this->scriptId);
        $user = $this->userId ? User::query()->find($this->userId) : null;

        $deploymentService->execute($script, $user, $this->triggeredVia);
    }
}
