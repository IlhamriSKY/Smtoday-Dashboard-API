<?php

namespace Tests\Feature\Http\Controllers\Web\Auth;

use Auth;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Socialite;
use Facades\Tests\Setup\UserFactory;
use Tests\TestCase;
use Tests\UpdatesSettings;
use Vanguard\Support\Enum\UserStatus;
use Vanguard\User;
use Laravel\Socialite\Contracts\User as SocialUserContract;
use Mockery as m;

class SocialAuthenticationTest extends TestCase
{
    use RefreshDatabase, UpdatesSettings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');
    }

    /** @test */
    public function social_login_for_new_user()
    {
        $this->setSettings(['reg_enabled' => true]);

        $socialUser = new StubSocialUser;

        $driver = m::mock(SocialiteProvider::class);
        $driver->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('foo')->andReturn($driver);

        $this->get("auth/foo/callback");

        $this->assertDatabaseHas('users', [
            'username' => null,
            'email' => $socialUser->getEmail(),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'status' => UserStatus::ACTIVE
        ]);

        $user = User::where('email', $socialUser->getEmail())->first();

        $this->assertDatabaseHas('social_logins', [
            'user_id' => $user->id,
            'provider' => 'foo',
            'provider_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar()
        ]);

        $this->assertEquals($user->id, Auth::id());
    }

    /** @test */
    public function social_login_for_new_user_if_registration_is_disabled()
    {
        $this->setSettings(['reg_enabled' => false]);

        $socialUser = new StubSocialUser;

        $driver = m::mock(SocialiteProvider::class);
        $driver->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('foo')->andReturn($driver);

        $this->get("auth/foo/callback")
            ->assertRedirect('/login');

        $this->assertSessionHasError('Only users who already created an account can log in.');
    }

    /** @test */
    public function social_login_for_banned_user()
    {
        $user = UserFactory::banned()->create();
        $socialUser = new StubSocialUser;

        $driver = m::mock(SocialiteProvider::class);
        $driver->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('foo')->andReturn($driver);

        DB::table('social_logins')->insert([
            'user_id' => $user->id,
            'provider' => 'foo',
            'provider_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'created_at' => \Carbon\Carbon::now()
        ]);

        $this->get("auth/foo/callback")
            ->assertRedirect('/login');

        $this->assertSessionHasError('Your account is banned by administrator.');
    }

    /** @test */
    public function social_login_for_existing_user()
    {
        $user = factory(User::class)->create();
        $socialUser = new StubSocialUser;

        $driver = m::mock(SocialiteProvider::class);
        $driver->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('foo')->andReturn($driver);

        DB::table('social_logins')->insert([
            'user_id' => $user->id,
            'provider' => 'foo',
            'provider_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'created_at' => \Carbon\Carbon::now()
        ]);

        $this->get("auth/foo/callback")
            ->assertRedirect('/');

        $this->assertEquals($user->id, Auth::id());
    }

    /** @test */
    public function missing_email_login()
    {
        $this->setSettings(['reg_enabled' => true]);

        $socialUser = new StubSocialUserWithoutEmail;
        $driver = m::mock(SocialiteProvider::class);
        $driver->shouldReceive('user')->andReturn($socialUser);
        Socialite::shouldReceive('driver')->with('foo')->andReturn($driver);

        $this->get("auth/foo/callback")
            ->assertRedirect('login');

        $this->assertSessionHasError("You have to provide your email address.");
    }

    /** @test */
    public function social_login_for_user_with_one_word_name()
    {
        $this->setSettings(['reg_enabled' => true]);

        $socialUser = new StubSocialUserWithOneWordName;

        $driver = m::mock(SocialiteProvider::class);
        $driver->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('foo')->andReturn($driver);

        $this->get("auth/foo/callback")
            ->assertRedirect('/');

        $this->assertDatabaseHas('users', [
            'username' => null,
            'email' => $socialUser->getEmail(),
            'first_name' => 'John',
            'last_name' => '',
            'status' => UserStatus::ACTIVE
        ]);

        $user = User::where('email', $socialUser->getEmail())->first();

        $this->assertDatabaseHas('social_logins', [
            'user_id' => $user->id,
            'provider' => 'foo',
            'provider_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar()
        ]);

        $this->assertEquals($user->id, Auth::id());
    }

}

class StubSocialUser implements SocialUserContract
{
    public function getId()
    {
        return '123';
    }

    public function getNickname()
    {
        return 'johndoe';
    }

    public function getName()
    {
        return 'John Doe';
    }

    public function getEmail()
    {
        return 'john@doe.com';
    }

    public function getAvatar()
    {
        return 'http://www.gravatar.com/avatar';
    }
}

class StubSocialUserWithoutEmail extends StubSocialUser
{
    public $email = null;

    public function getEmail()
    {
        return $this->email;
    }
}

class StubSocialUserWithOneWordName extends StubSocialUser
{
    public function getName()
    {
        return 'John';
    }
}
