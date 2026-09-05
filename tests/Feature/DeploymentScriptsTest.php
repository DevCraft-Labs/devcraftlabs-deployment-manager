<?php

namespace Tests\Feature;

use App\Contracts\Services\DeploymentServiceInterface;
use App\Models\DeploymentExecution;
use App\Models\DeploymentScript;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Spatie\Permission\Models\Role;
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
}