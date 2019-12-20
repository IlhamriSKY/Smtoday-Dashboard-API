<?php

namespace Vanguard\UserActivity\Tests\Unit\Listeners;

use Tests\UpdatesSettings;

class UserEventsSubscriberTest extends ListenerTestCase
{
    use UpdatesSettings;

    protected $theUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->theUser = factory(\Vanguard\User::class)->create();
    }

    /** @test */
    public function onLogin()
    {
        event(new \Vanguard\Events\User\LoggedIn);
        $this->assertMessageLogged('Logged in.');
    }

    /** @test */
    public function onLogout()
    {
        event(new \Vanguard\Events\User\LoggedOut());
        $this->assertMessageLogged('Logged out.');
    }

    /** @test */
    public function onRegister()
    {
        $this->setSettings([
            'reg_enabled' => true,
            'reg_email_confirmation' => true,
        ]);

        $user = factory(\Vanguard\User::class)->create();

        event(new \Illuminate\Auth\Events\Registered($user));

        $this->assertMessageLogged('Created an account.', $user);
    }

    /** @test */
    public function onAvatarChange()
    {
        event(new \Vanguard\Events\User\ChangedAvatar);
        $this->assertMessageLogged('Updated profile avatar.');
    }

    /** @test */
    public function onProfileDetailsUpdate()
    {
        event(new \Vanguard\Events\User\UpdatedProfileDetails);
        $this->assertMessageLogged('Updated profile details.');
    }

    /** @test */
    public function onDelete()
    {
        event(new \Vanguard\Events\User\Deleted($this->theUser));

        $message = sprintf(
            "Deleted user %s.",
            $this->theUser->present()->nameOrEmail
        );

        $this->assertMessageLogged($message);
    }

    /** @test */
    public function onBan()
    {
        event(new \Vanguard\Events\User\Banned($this->theUser));

        $message = sprintf(
            "Banned user %s.",
            $this->theUser->present()->nameOrEmail
        );

        $this->assertMessageLogged($message);
    }

    /** @test */
    public function onUpdateByAdmin()
    {
        event(new \Vanguard\Events\User\UpdatedByAdmin($this->theUser));

        $message = sprintf(
            "Updated profile details for %s.",
            $this->theUser->present()->nameOrEmail
        );

        $this->assertMessageLogged($message);
    }

    /** @test */
    public function onCreate()
    {
        event(new \Vanguard\Events\User\Created($this->theUser));

        $message = sprintf(
            "Created an account for user %s.",
            $this->theUser->present()->nameOrEmail
        );

        $this->assertMessageLogged($message);
    }

    /** @test */
    public function onSettingsUpdate()
    {
        event(new \Vanguard\Events\Settings\Updated);
        $this->assertMessageLogged('Updated website settings.');
    }

    /** @test */
    public function onTwoFactorEnable()
    {
        event(new \Vanguard\Events\User\TwoFactorEnabled);
        $this->assertMessageLogged('Enabled Two-Factor Authentication.');
    }

    /** @test */
    public function onTwoFactorDisable()
    {
        event(new \Vanguard\Events\User\TwoFactorDisabled);
        $this->assertMessageLogged('Disabled Two-Factor Authentication.');
    }

    /** @test */
    public function onTwoFactorEnabledByAdmin()
    {
        event(new \Vanguard\Events\User\TwoFactorEnabledByAdmin($this->theUser));

        $message = sprintf(
            "Enabled Two-Factor Authentication for user %s.",
            $this->theUser->present()->nameOrEmail
        );

        $this->assertMessageLogged($message);
    }

    /** @test */
    public function onTwoFactorDisabledByAdmin()
    {
        event(new \Vanguard\Events\User\TwoFactorDisabledByAdmin($this->theUser));

        $message = sprintf(
            "Disabled Two-Factor Authentication for user %s.",
            $this->theUser->present()->nameOrEmail
        );

        $this->assertMessageLogged($message);
    }

    /** @test */
    public function onPasswordResetEmailRequest()
    {
        event(new \Vanguard\Events\User\RequestedPasswordResetEmail($this->user));
        $this->assertMessageLogged("Requested password reset email.");
    }

    /** @test */
    public function onPasswordReset()
    {
        event(new \Illuminate\Auth\Events\PasswordReset($this->user));
        $this->assertMessageLogged("Reseted password using \"Forgot Password\" option.");
    }

    /** @test */
    public function onStartImpersonating()
    {
        $impersonated = factory(\Vanguard\User::class)->create([
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        event(new \Lab404\Impersonate\Events\TakeImpersonation($this->user, $impersonated));

        $this->assertMessageLogged("Started impersonating user John Doe (ID: {$impersonated->id})");
    }

    /** @test */
    public function onStopImpersonating()
    {
        $impersonated = factory(\Vanguard\User::class)->create([
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        event(new \Lab404\Impersonate\Events\LeaveImpersonation($this->user, $impersonated));

        $this->assertMessageLogged("Stopped impersonating user John Doe (ID: {$impersonated->id})");
    }
}
