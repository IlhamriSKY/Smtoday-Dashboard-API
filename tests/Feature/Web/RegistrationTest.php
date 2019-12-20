<?php

namespace Tests\Feature\Http\Controllers\Web\Auth;

use Facades\Tests\Setup\UserFactory;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Mail;
use Tests\TestCase;
use Tests\UpdatesSettings;
use Vanguard\Support\Enum\UserStatus;
use Vanguard\User;

class RegistrationTest extends TestCase
{
    use RefreshDatabase, UpdatesSettings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');
    }

    /** @test */
    public function when_registration_is_disabled_a_visitor_cannot_see_the_registration_form()
    {
        $this->setSettings(['reg_enabled' => false]);

        $this->get('register')->assertStatus(404);

        $this->get('login')->assertDontSee('You don\'t have an account?');
    }

    /** @test */
    public function registration_with_email_confirmation()
    {
        $this->setSettings([
            'reg_enabled' => true,
            'reg_email_confirmation' => true,
            'registration.captcha.enabled' => false,
            'tos' => true
        ]);

        Notification::fake();

        $data = $this->getRegistrationFormStubData();

        $this->post('/register', $data)->assertRedirect('/');

        $expected = Arr::except($data, ['password', 'password_confirmation', 'tos']);
        $expected += ['status' => UserStatus::UNCONFIRMED];

        $this->assertDatabaseHas('users', $expected);

        $user = User::where('email', $data['email'])->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** @test */
    public function registration_without_email_confirmation()
    {
        $this->setSettings([
            'reg_enabled' => true,
            'reg_email_confirmation' => false,
            'notifications_signup_email' => false,
            'registration.captcha.enabled' => false,
            'tos' => true
        ]);

        Notification::fake();

        $data = $this->getRegistrationFormStubData();

        $this->post('/register', $data)->assertRedirect('/');

        $expected = Arr::except($data, ['password', 'password_confirmation', 'tos']);
        $expected += ['status' => UserStatus::ACTIVE];

        $this->assertDatabaseHas('users', $expected);

        Notification::assertNotSentTo(
            User::where('email', $data['email'])->first(),
            VerifyEmail::class
        );
    }

    /** @test */
    public function email_notification_is_being_sent_when_new_user_registers()
    {
        $this->setSettings([
            'app_name' => 'foo',
            'reg_enabled' => true,
            'reg_email_confirmation' => false,
            'notifications_signup_email' => true,
            'registration.captcha.enabled' => false,
            'tos' => true
        ]);

        Mail::fake();

        $admin = UserFactory::admin()->email('john.doe@test.com')->create();
        $user1 = UserFactory::user()->email('jane.doe@test.com')->create();
        $user2 = UserFactory::user()->email('josh.doe@test.com')->create();

        $this->post('/register', $this->getRegistrationFormStubData());

        Mail::assertQueued(\Vanguard\Mail\UserRegistered::class, 2);
    }

    /** @test */
    public function redirect_to_custom_page_after_login()
    {
        UserFactory::withCredentials('foo', 'bar')->create();

        $this->post('/login', [
            'username' => 'foo',
            'password' => 'bar',
            'to' => 'http://www.google.com'
        ])->assertRedirect('http://www.google.com');
    }

    /** @test */
    public function custom_redirect_page_is_available_after_failed_login_attempt()
    {
        $to = 'http://www.google.com';

        $this->post('/login', [
            'username' => 'foo',
            'password' => 'bar',
            'to' => 'http://www.google.com'
        ])->assertRedirect('login?to=' . $to);
    }

    /** @test */
    public function access_to_auth_pages_is_not_allowed_for_authenticated_users()
    {
        $this->setSettings([
            'reg_enabled' => true,
            '2fa.enabled' => true,
            'forgot_password' => true
        ]);

        $user = UserFactory::withCredentials('foo', 'bar')->create();
        $this->be($user);

        $forbiddenGetRoutes = [
            'login', 'register', 'password/reset', 'password/reset/123',
            'auth/facebook/login', 'auth/facebook/callback',
        ];

        foreach ($forbiddenGetRoutes as $route) {
            $this->get($route)->assertRedirect('/');
        }

        $this->get('auth/two-factor-authentication')->assertRedirect("/login");
    }

    private function getRegistrationFormStubData()
    {
        return [
            'email' => 'test@test.com',
            'username' => 'johndoe',
            'password' => '123123123',
            'password_confirmation' => '123123123',
            'tos' => 1
        ];
    }
}
