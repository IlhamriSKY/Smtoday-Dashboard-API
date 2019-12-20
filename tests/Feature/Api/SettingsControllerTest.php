<?php

namespace Tests\Feature\Http\Controllers\Api;

use Facades\Tests\Setup\UserFactory;
use Setting;
use Tests\Feature\ApiTestCase;
use Vanguard\User;

class SettingsControllerTest extends ApiTestCase
{
    /** @test */
    public function only_authenticated_users_can_view_app_settings()
    {
        $this->getJson('/api/settings')->assertStatus(401);
    }

    /** @test */
    public function get_settings_without_permission()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/settings')
            ->assertStatus(403);
    }

    /** @test */
    public function get_settings()
    {
        $user = UserFactory::withPermissions('settings.general')->create();

        $this->actingAs($user, 'api')
            ->getJson("/api/settings")
            ->assertOk()
            ->assertJson(Setting::all());
    }
}
