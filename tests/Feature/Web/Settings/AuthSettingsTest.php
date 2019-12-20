<?php

namespace Tests\Feature\Http\Controllers\Web;

use Facades\Tests\Setup\RoleFactory;
use Facades\Tests\Setup\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Setting;
use Tests\TestCase;

class AuthSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');
    }

    /** @test */
    public function update_auth_settings()
    {
        Setting::set('app_name', 'bar');

        $data = $this->getAuthSettingsData();

        $this->actingAsAdmin()
            ->from('/settings/auth')
            ->post('/settings/auth', $data)
            ->assertRedirect('/settings/auth');

        $this->assertAuthSettingsUpdated($data);
    }

    /** @test */
    public function only_users_with_appropriate_permission_can_update_auth_settings()
    {
        Setting::set('app_name', 'bar');

        $roleA = RoleFactory::create();
        $roleB = RoleFactory::withPermissions('settings.auth')->create();

        $userA = UserFactory::role($roleA)->create();
        $userB = UserFactory::role($roleB)->create();

        $data = $this->getAuthSettingsData();

        $this->actingAs($userA)
            ->from('/settings/auth')
            ->post('/settings/auth', $data)
            ->assertStatus(403);

        $this->assertAuthSettingsNotUpdated($data);

        $this->actingAs($userB)
            ->from('/settings/auth')
            ->post('/settings/auth', $data)
            ->assertRedirect('/settings/auth');

        $this->assertAuthSettingsUpdated($data);
    }

    private function assertAuthSettingsUpdated(array $data)
    {
        $this->assertEquals($data['remember_me'], Setting::get('remember_me'));
        $this->assertEquals($data['forgot_password'], Setting::get('forgot_password'));
        $this->assertEquals($data['login_reset_token_lifetime'], Setting::get('login_reset_token_lifetime'));
        $this->assertEquals($data['throttle_enabled'], Setting::get('throttle_enabled'));
        $this->assertEquals($data['throttle_attempts'], Setting::get('throttle_attempts'));
        $this->assertEquals($data['throttle_lockout_time'], Setting::get('throttle_lockout_time'));
        $this->assertEquals($data['reg_enabled'], Setting::get('reg_enabled'));
        $this->assertEquals($data['tos'], Setting::get('tos'));
        $this->assertEquals($data['reg_email_confirmation'], Setting::get('reg_email_confirmation'));
    }

    private function assertAuthSettingsNotUpdated(array $data)
    {
        $this->assertNotEquals($data['remember_me'], Setting::get('remember_me'));
        $this->assertNotEquals($data['forgot_password'], Setting::get('forgot_password'));
        $this->assertNotEquals($data['login_reset_token_lifetime'], Setting::get('login_reset_token_lifetime'));
        $this->assertNotEquals($data['throttle_enabled'], Setting::get('throttle_enabled'));
        $this->assertNotEquals($data['throttle_attempts'], Setting::get('throttle_attempts'));
        $this->assertNotEquals($data['throttle_lockout_time'], Setting::get('throttle_lockout_time'));
        $this->assertNotEquals($data['reg_enabled'], Setting::get('reg_enabled'));
        $this->assertNotEquals($data['tos'], Setting::get('tos'));
        $this->assertNotEquals($data['reg_email_confirmation'], Setting::get('reg_email_confirmation'));
    }

    private function getAuthSettingsData()
    {
        return [
            'remember_me' => 1,
            'forgot_password' => 1,
            'login_reset_token_lifetime' => 123,
            'throttle_enabled' => 1,
            'throttle_attempts' => 10,
            'throttle_lockout_time' => 2,
            'reg_enabled' => 1,
            'tos' => 1,
            'reg_email_confirmation' => 1
        ];
    }
}
