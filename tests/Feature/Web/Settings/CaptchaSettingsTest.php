<?php

namespace Tests\Feature\Http\Controllers\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Setting;
use Tests\TestCase;

class CaptchaSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');
    }

    /** @test */
    public function enable_captcha()
    {
        Setting::set('registration.captcha.enabled', false);

        $this->assertFalse(Setting::get('registration.captcha.enabled'));

        $this->actingAsAdmin()
            ->from('/settings/auth')
            ->post('/settings/auth/registration/captcha/enable')
            ->assertRedirect('/settings/auth');

        $this->assertTrue(Setting::get('registration.captcha.enabled'));
    }

    /** @test */
    public function disable_two_factor()
    {
        Setting::set('registration.captcha.enabled', true);

        $this->assertTrue(Setting::get('registration.captcha.enabled'));

        $this->actingAsAdmin()
            ->from('/settings/auth')
            ->post('/settings/auth/registration/captcha/disable')
            ->assertRedirect('/settings/auth');

        $this->assertFalse(Setting::get('registration.captcha.enabled'));
    }
}
