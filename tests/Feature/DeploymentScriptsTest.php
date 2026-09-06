<?php

namespace Tests\Feature;

use App\Contracts\Services\DeploymentServiceInterface;
use App\Contracts\Services\TelegramServiceInterface;
use App\Models\DeploymentExecution;
use App\Models\DeploymentScript;
use App\Models\TelegramConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Mockery;
use Spatie\Permission\Models\Role;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class DeploymentScriptsTest extends TestCase
{
    use RefreshDatabase;

    public function test_running_a_script_identifies_its_deployment_target(): void
    {
        $script = $this->script();
        $service = Mockery::mock(DeploymentServiceInterface::class);
        $service->shouldReceive('queue')->once()->andReturn(new DeploymentExecution());
        $this->app->instance(DeploymentServiceInterface::class, $service);

        $this->actingAs($this->owner())
            ->post(route('deployment-scripts.run', $script))
            ->assertRedirect()
            ->assertSessionHas('status', "Deployment of {$script->name} to {$script->working_directory} has been queued.");
    }

    public function test_deployment_index_displays_the_latest_execution(): void
    {
        $script = $this->script();
        DeploymentExecution::query()->create([
            'deployment_script_id' => $script->id,
            'triggered_via' => 'manual',
            'status' => 'succeeded',
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
            'is_success' => true,
        ]);

        $this->actingAs($this->owner())
            ->get(route('deployment-scripts.index'))
            ->assertOk()
            ->assertSee('Last Deployed')
            ->assertSee('Succeeded');
    }

    public function test_successful_deployment_notification_includes_the_latest_commit_message(): void
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'deployment-git-' . uniqid();
        File::ensureDirectoryExists($directory);

        try {
            $this->runGit($directory, ['init']);
            $this->runGit($directory, ['config', 'user.email', 'deployments@example.test']);
            $this->runGit($directory, ['config', 'user.name', 'Deployment Test']);
            File::put($directory . DIRECTORY_SEPARATOR . 'README.md', "Deployment test\n");
            $this->runGit($directory, ['add', 'README.md']);
            $this->runGit($directory, ['commit', '-m', 'Deploy committed changes']);
            $latestCommit = trim($this->runGit($directory, ['log', '-1', '--pretty=format:%h %s']));
            $telegram = TelegramConnection::query()->create([
                'name' => 'Deployment Alerts',
                'bot_token' => 'test-token',
                'chat_id' => '12345',
                'status' => true,
            ]);
            $script = DeploymentScript::query()->create([
                'name' => 'Git Deployment',
                'working_directory' => $directory,
                'script_content' => 'echo deploy',
                'timeout' => 60,
                'telegram_connection_id' => $telegram->id,
            ]);
            $telegramService = new class implements TelegramServiceInterface
            {
                public array $messages = [];

                public function sendMessage(TelegramConnection $connection, string $message): array
                {
                    $this->messages[] = compact('connection', 'message');

                    return ['ok' => true];
                }

                public function test(TelegramConnection $connection): array
                {
                    return ['ok' => true];
                }
            };
            $this->app->instance(TelegramServiceInterface::class, $telegramService);

            $result = $this->app->make(DeploymentServiceInterface::class)->execute($script, $this->owner());

            $this->assertTrue($result->success);
            $this->assertCount(1, $telegramService->messages);
            $this->assertTrue($telegramService->messages[0]['connection']->is($telegram));
            $this->assertStringContainsString('*Deployment succeeded*', $telegramService->messages[0]['message']);
            $this->assertStringContainsString('Latest commit: ' . $latestCommit, $telegramService->messages[0]['message']);
        } finally {
            File::deleteDirectory($directory);
        }
    }

    private function owner(): User
    {
        $role = Role::findOrCreate('Owner');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function script(): DeploymentScript
    {
        return DeploymentScript::query()->create([
            'name' => 'Website Deployment',
            'working_directory' => '/var/www/website',
            'script_content' => 'echo deploy',
            'timeout' => 60,
        ]);
    }

    private function runGit(string $directory, array $arguments): string
    {
        $process = new Process(['git', ...$arguments], $directory);
        $process->run();
        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());

        return $process->getOutput();
    }
}