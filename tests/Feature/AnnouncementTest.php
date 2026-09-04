<?php

namespace Tests\Feature;

use App\Models\ApplicationSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_publish_a_sanitized_dashboard_announcement(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->patch(route('settings.update'), [
            'server_name' => 'Deployment Manager',
            'timezone' => 'UTC',
            'retention_days' => 30,
            'announcement_html' => '<p onclick="alert(1)"><strong>Maintenance window</strong></p><script>alert(1)</script>',
        ])->assertRedirect();

        $announcement = ApplicationSetting::query()->value('announcement_html');
        $this->assertStringContainsString('<strong>Maintenance window</strong>', $announcement);
        $this->assertStringNotContainsString('onclick', $announcement);
        $this->assertStringNotContainsString('<script', $announcement);

        $this->actingAs($owner)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Announcement')
            ->assertSee('Maintenance window');
    }

    private function owner(): User
    {
        $role = Role::findOrCreate('Owner');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}