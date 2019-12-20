<?php

namespace Tests\Feature\Http\Controllers\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Setting;
use Tests\TestCase;

class TwoFactorSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');
    }

    /** @test */
    public function enable_two_factor()
    {
        Setting::set('2fa.enabled', false);

        $this->assertFalse(Setting::get('2fa.enabled'));

        $this->actingAsAdmin()
            ->from('/settings/auth')
            ->post('/settings/auth/2fa/enable')
            ->assertRedirect('/settings/auth');

        $this->assertTrue(Setting::get('2fa.enabled'));
    }

    /** @test */
    public function disable_two_factor()
    {
        Setting::set('2fa.enabled', true);

        $this->assertTrue(Setting::get('2fa.enabled'));

        $this->actingAsAdmin()
            ->from('/settings/auth')
            ->post('/settings/auth/2fa/disable')
            ->assertRedirect('/settings/auth');

        $this->assertFalse(Setting::get('2fa.enabled'));
    }
}
