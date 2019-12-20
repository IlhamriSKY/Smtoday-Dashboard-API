<?php

namespace Tests\Feature\Http\Controllers\Web;

use Facades\Tests\Setup\RoleFactory;
use Facades\Tests\Setup\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Setting;
use Tests\TestCase;

class GeneralSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');
    }

    /** @test */
    public function update_general_settings()
    {
        Setting::set('app_name', 'bar');

        $this->assertEquals('bar', Setting::get('app_name'));

        $this->actingAsAdmin()
            ->from('/settings/general')
            ->post('/settings/general', ['app_name' => 'foo'])
            ->assertRedirect('/settings/general');

        $this->assertEquals('foo', Setting::get('app_name'));
    }

    /** @test */
    public function only_users_with_appropriate_permission_can_update_general_settings()
    {
        Setting::set('app_name', 'bar');

        $roleA = RoleFactory::create();
        $roleB = RoleFactory::withPermissions('settings.general')->create();

        $userA = UserFactory::role($roleA)->create();
        $userB = UserFactory::role($roleB)->create();

        $this->actingAs($userA)
            ->from('/settings/general')
            ->post('/settings/general', ['app_name' => 'foo'])
            ->assertStatus(403);

        $this->assertEquals('bar', Setting::get('app_name'));

        $this->actingAs($userB)
            ->from('/settings/general')
            ->post('/settings/general', ['app_name' => 'foo'])
            ->assertRedirect('/settings/general');

        $this->assertEquals('foo', Setting::get('app_name'));
    }
}
