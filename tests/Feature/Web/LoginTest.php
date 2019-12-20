<?php

namespace Tests\Feature\Http\Controllers\Web\Auth;

use Authy;
use Carbon\Carbon;
use Facades\Tests\Setup\UserFactory;
use Illuminate\Cache\RateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery as m;
use Setting;
use Tests\TestCase;
use Tests\UpdatesSettings;
use Vanguard\Events\User\LoggedIn;
use Vanguard\User;

class LoginTest extends TestCase
{
    use RefreshDatabase, UpdatesSettings;

    /** @test */
    public function successful_login()
    {
        $user = UserFactory::withCredentials('foo', 'bar')->create();

        $this->loginUser('foo', 'bar')
            ->assertRedirect('/');

        $this->assertTrue(auth()->check());
        $this->assertTrue($user->is(auth()->user()));
    }

    /** @test */
    public function last_login_timestamp_is_updated_after_successful_login()
    {
        $testDate = Carbon::now();

        Carbon::setTestNow($testDate);

        $user = UserFactory::withCredentials('foo', 'bar')->create();

        $this->assertNull($user->last_login);

        $this->loginUser('foo', 'bar');

        $this->assertEquals($testDate->timestamp, $user->fresh()->last_login->timestamp);
    }

    /** @test */
    public function login_with_wrong_credentials_will_fail()
    {
        $this->loginUser('foo', 'bar')
            ->assertRedirect('/login');

        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function country_id_remains_the_same_after_login()
    {
        $user = factory(User::class)->create([
            'username' => 'foo',
            'password' => 'bar',
            'country_id' => 688
        ]);

        $this->loginUser('foo', 'bar')
            ->assertRedirect("/");

        $this->assertEquals(688, $user->fresh()->country_id);
    }

    /** @test */
    public function throttling()
    {
        $this->setSettings([
            'throttle_enabled' => true,
            'throttle_attempts' => 3,
            'throttle_lockout_time' => 2 // 2 minutes
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->loginUser('foo', 'bar');
        }

        $this->loginUser('foo', 'bar')
            ->assertRedirect('login')
            ->assertSessionHasErrors('username');

        $this->assertTrue(app(RateLimiter::class)->tooManyAttempts('foo|127.0.0.1', 3));
    }

    /** @test */
    public function login_with_remember()
    {
        $user = UserFactory::withCredentials('foo', 'bar')->create();

        Setting::set('remember_me', false);

        $this->get('login')
            ->assertDontSeeText('name="remember"');

        Setting::set('remember_me', true);

        $this->get('login')
            ->assertSee('name="remember"');

        $this->loginUser('foo', 'bar', true)
            ->assertRedirect('/');

        $this->assertNotNull($user->fresh()->remember_token);
        $this->assertNotNull($user->fresh()->last_login);
    }

    /** @test */
    public function banned_user_cannot_log_in()
    {
        UserFactory::withCredentials('foo', 'bar')->banned()->create();

        $this->loginUser('foo', 'bar')
            ->assertRedirect('/login');

        $this->assertSessionHasError("Your account is banned by administrator.");
    }

    /** @test */
    public function login_with_2fa_enabled()
    {
        $this->withoutExceptionHandling();
        $this->setSettings(['2fa.enabled' => true]);

        $this->expectsEvents(LoggedIn::class);

        $user = UserFactory::withCredentials('foo', 'bar')->create();

        Authy::shouldReceive('isEnabled')->andReturn(true);
        Authy::shouldReceive('tokenIsValid')->with(m::any(), '123')->andReturn(true);

        $this->loginUser('foo', 'bar')
            ->assertRedirect('auth/two-factor-authentication')
            ->assertSessionHas('auth.2fa.id', $user->id);

        $this->post('auth/two-factor-authentication', ['token' => '123'])
            ->assertRedirect('/');

        $this->assertTrue(auth()->check());
    }

    /** @test */
    public function login_with_wrong_2fa_token()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $user = UserFactory::withCredentials('foo', 'bar')->create();

        Authy::shouldReceive('isEnabled')->andReturn(true);
        Authy::shouldReceive('tokenIsValid')->with(m::any(), '123')->andReturn(false);

        $this->loginUser('foo', 'bar')
            ->assertRedirect('auth/two-factor-authentication')
            ->assertSessionHas('auth.2fa.id', $user->id);

        $this->post('auth/two-factor-authentication', ['token' => '123'])
            ->assertRedirect('login');

        $this->assertSessionHasError('2FA Token is invalid!');
    }

    private function loginUser($username, $password, $remember = false)
    {
        return $this->post('/login', [
            'username' => $username,
            'password' => $password,
            'remember' => $remember
        ]);
    }
}
