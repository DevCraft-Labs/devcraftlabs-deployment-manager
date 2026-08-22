<?php

namespace Tests\Feature;

use App\Models\DeploymentScript;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeploymentEnvironmentFileTest extends TestCase
{
    use RefreshDatabase;

    private string $projectDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->projectDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'deployment-manager-' . uniqid();
        File::ensureDirectoryExists($this->projectDirectory);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->projectDirectory);

        parent::tearDown();
    }

    public function test_owner_can_read_and_update_a_project_environment_file(): void
    {
        File::put($this->projectDirectory . DIRECTORY_SEPARATOR . '.env', "APP_NAME=Before\n");
        $script = $this->scriptForDirectory($this->projectDirectory);

        $this->actingAs($this->owner())
            ->get(route('deployment-scripts.environment.edit', $script))
            ->assertOk()
            ->assertSee('APP_NAME=Before', false);

        $this->put(route('deployment-scripts.environment.update', $script), [
            'contents' => "APP_NAME=After\nAPP_ENV=production\n",
        ])->assertRedirect(route('deployment-scripts.environment.edit', $script));

        $this->assertSame("APP_NAME=After\nAPP_ENV=production\n", File::get($this->projectDirectory . DIRECTORY_SEPARATOR . '.env'));
        $this->assertDatabaseHas('activity_audits', [
            'action' => 'script.environment.update',
            'entity_type' => DeploymentScript::class,
            'entity_id' => $script->id,
        ]);
    }

    public function test_environment_editor_rejects_an_unavailable_project_directory(): void
    {
        $script = $this->scriptForDirectory($this->projectDirectory . '-missing');

        $this->actingAs($this->owner())
            ->get(route('deployment-scripts.environment.edit', $script))
            ->assertNotFound();
    }

    private function owner(): User
    {
        $role = Role::findOrCreate('Owner');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function scriptForDirectory(string $directory): DeploymentScript
    {
        return DeploymentScript::query()->create([
            'name' => 'Environment Test ' . uniqid(),
            'working_directory' => $directory,
            'script_content' => 'echo deploy',
            'timeout' => 60,
        ]);
    }
}