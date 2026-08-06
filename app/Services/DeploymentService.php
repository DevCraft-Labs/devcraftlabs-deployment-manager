<?php

namespace App\Services;

use App\Contracts\Repositories\DeploymentExecutionRepositoryInterface;
use App\Contracts\Services\DeploymentServiceInterface;
use App\DTOs\DeploymentResultData;
use App\Jobs\RunDeploymentScriptJob;
use App\Jobs\SendTelegramNotificationJob;
use App\Models\ApplicationSetting;
use App\Models\DeploymentExecution;
use App\Models\DeploymentScript;
use App\Models\TelegramConnection;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Throwable;

class DeploymentService implements DeploymentServiceInterface
{
    public function __construct(private readonly DeploymentExecutionRepositoryInterface $executionRepository)
    {
    }

    public function queue(DeploymentScript $script, ?User $user, string $triggeredVia = 'manual'): DeploymentExecution
    {
        $execution = $this->executionRepository->create([
            'deployment_script_id' => $script->id,
            'triggered_by_user_id' => $user?->id,
            'triggered_via' => $triggeredVia,
            'status' => 'queued',
            'started_at' => now(),
            'is_success' => false,
        ]);

        RunDeploymentScriptJob::dispatch($script->id, $user?->id, $triggeredVia, $execution->id);

        Log::channel('deployment')->info('Deployment queued', [
            'script_id' => $script->id,
            'execution_id' => $execution->id,
            'triggered_via' => $triggeredVia,
        ]);

        return $execution;
    }

    public function execute(DeploymentScript $script, ?User $user, string $triggeredVia = 'manual', ?DeploymentExecution $execution = null): DeploymentResultData
    {
        $execution ??= $this->executionRepository->create([
            'deployment_script_id' => $script->id,
            'triggered_by_user_id' => $user?->id,
            'triggered_via' => $triggeredVia,
            'status' => 'running',
            'started_at' => now(),
            'is_success' => false,
        ]);

        $this->executionRepository->update($execution, [
            'status' => 'running',
            'started_at' => now(),
        ]);

        $started = microtime(true);

        try {
            $process = Process::fromShellCommandline($script->script_content, $script->working_directory);
            $process->setTimeout($script->timeout);
            $process->run();

            $finishedAt = now();
            $duration = (int) round((microtime(true) - $started) * 1000);
            $isSuccess = $process->isSuccessful();

            $execution = $this->executionRepository->update($execution, [
                'finished_at' => $finishedAt,
                'duration_ms' => $duration,
                'exit_code' => $process->getExitCode(),
                'is_success' => $isSuccess,
                'status' => $isSuccess ? 'succeeded' : 'failed',
                'stdout' => $process->getOutput(),
                'stderr' => $process->getErrorOutput(),
                'failure_report' => $isSuccess ? null : $this->buildFailureReport(
                    $execution,
                    $script,
                    $finishedAt,
                    $duration,
                    $process->getExitCode(),
                    $process->getErrorOutput(),
                ),
            ]);

            Log::channel('deployment')->info('Deployment executed', [
                'script_id' => $script->id,
                'execution_id' => $execution->id,
                'success' => $isSuccess,
                'exit_code' => $process->getExitCode(),
            ]);

            $this->queueCompletionNotification($script, $execution, $isSuccess);

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

            $execution = $this->executionRepository->update($execution, [
                'finished_at' => now(),
                'duration_ms' => $duration,
                'is_success' => false,
                'status' => 'failed',
                'stderr' => $e->getMessage(),
                'failure_report' => $this->buildFailureReport($execution, $script, now(), $duration, null, $e->getMessage()),
            ]);

            Log::channel('deployment')->error('Deployment failed unexpectedly', [
                'script_id' => $script->id,
                'execution_id' => $execution->id,
                'error' => $e->getMessage(),
            ]);

            $this->queueCompletionNotification($script, $execution, false);

            return new DeploymentResultData($execution->id, false, null, $duration, '', $e->getMessage());
        }
    }

    private function queueCompletionNotification(DeploymentScript $script, DeploymentExecution $execution, bool $isSuccess): void
    {
        $connection = $script->telegram_connection_id
            ? $script->telegramConnection
            : ApplicationSetting::query()->first()?->defaultTelegram;

        if (!$connection instanceof TelegramConnection || !$connection->status) {
            return;
        }

        $outcome = $isSuccess ? 'succeeded' : 'failed';
        $duration = $execution->duration_ms === null ? 'unknown' : number_format($execution->duration_ms / 1000, 2) . 's';
        $exitCode = $execution->exit_code === null ? 'n/a' : (string) $execution->exit_code;

        SendTelegramNotificationJob::dispatch($connection->id, sprintf(
            "*Deployment %s*\nScript: %s\nSource: %s\nDuration: %s\nExit code: %s\nExecution: #%d",
            $outcome,
            $this->escapeTelegramMarkdown($script->name),
            $this->escapeTelegramMarkdown($execution->triggered_via),
            $duration,
            $exitCode,
            $execution->id,
        ));
    }

    private function buildFailureReport(DeploymentExecution $execution, DeploymentScript $script, $finishedAt, int $duration, ?int $exitCode, string $stderr): string
    {
        $reason = trim($stderr) !== ''
            ? trim($stderr)
            : 'The process exited with a non-zero status but did not provide standard error output.';

        return implode("\n", [
            'DEPLOYMENT FAILURE REPORT',
            'Execution ID: ' . $execution->id,
            'Script: ' . $script->name,
            'Working directory: ' . $script->working_directory,
            'Triggered via: ' . $execution->triggered_via,
            'Finished at: ' . $finishedAt->toDateTimeString(),
            'Duration: ' . $duration . ' ms',
            'Exit code: ' . ($exitCode === null ? 'unavailable' : $exitCode),
            '',
            'Failure reason:',
            $reason,
        ]);
    }

    private function escapeTelegramMarkdown(string $value): string
    {
        return str_replace(['_', '*', '`', '['], ['\\_', '\\*', '\\`', '\\['], $value);
    }
}
