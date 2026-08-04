<?php

namespace App\Services;

use App\Contracts\Repositories\DeploymentExecutionRepositoryInterface;
use App\Contracts\Services\DeploymentServiceInterface;
use App\DTOs\DeploymentResultData;
use App\Jobs\RunDeploymentScriptJob;
use App\Models\DeploymentScript;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Throwable;

class DeploymentService implements DeploymentServiceInterface
{
    public function __construct(private readonly DeploymentExecutionRepositoryInterface $executionRepository)
    {
    }

    public function queue(DeploymentScript $script, ?User $user, string $triggeredVia = 'manual'): void
    {
        RunDeploymentScriptJob::dispatch($script->id, $user?->id, $triggeredVia);
    }

    public function execute(DeploymentScript $script, ?User $user, string $triggeredVia = 'manual'): DeploymentResultData
    {
        $execution = $this->executionRepository->create([
            'deployment_script_id' => $script->id,
            'triggered_by_user_id' => $user?->id,
            'triggered_via' => $triggeredVia,
            'started_at' => now(),
            'is_success' => false,
        ]);

        $started = microtime(true);

        try {
            $process = Process::fromShellCommandline($script->script_content, $script->working_directory);
            $process->setTimeout($script->timeout);
            $process->run();

            $finishedAt = now();
            $duration = (int) round((microtime(true) - $started) * 1000);
            $isSuccess = $process->isSuccessful();

            $this->executionRepository->update($execution, [
                'finished_at' => $finishedAt,
                'duration_ms' => $duration,
                'exit_code' => $process->getExitCode(),
                'is_success' => $isSuccess,
                'stdout' => $process->getOutput(),
                'stderr' => $process->getErrorOutput(),
            ]);

            Log::channel('deployment')->info('Deployment executed', [
                'script_id' => $script->id,
                'execution_id' => $execution->id,
                'success' => $isSuccess,
                'exit_code' => $process->getExitCode(),
            ]);

            return new DeploymentResultData(
                $execution->id,
                $isSuccess,
                $process->getExitCode(),
                $duration,
                $process->getOutput(),
                $process->getErrorOutput(),
            );
        } catch (Throwable $e) {
            $duration = (int) round((microtime(true) - $started) * 1000);

            $this->executionRepository->update($execution, [
                'finished_at' => now(),
                'duration_ms' => $duration,
                'is_success' => false,
                'stderr' => $e->getMessage(),
            ]);

            Log::channel('deployment')->error('Deployment failed unexpectedly', [
                'script_id' => $script->id,
                'execution_id' => $execution->id,
                'error' => $e->getMessage(),
            ]);

            return new DeploymentResultData($execution->id, false, null, $duration, '', $e->getMessage());
        }
    }
}
