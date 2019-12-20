<?php

namespace Tests\Feature\Http\Controllers\Web;

use Authy;
use Facades\Tests\Setup\UserFactory;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\UpdatesSettings;
use Vanguard\Events\User\TwoFactorEnabled;
use Vanguard\Events\User\TwoFactorEnabledByAdmin;
use Vanguard\Repositories\User\UserRepository;
use Vanguard\User;

class TwoFactorAuthTest extends TestCase
{
    use RefreshDatabase, UpdatesSettings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');
    }

    /** @test */
    public function the_2fa_form_is_visible_on_profile_page_if_2fa_is_enabled()
    {
        config(['services.authy.key' => 'test']);

        $this->setSettings(['2fa.enabled' => false]);

        $this->actingAsAdmin()
            ->get("profile")
            ->assertDontSee('Two-Factor Authentication');

        $this->setSettings(['2fa.enabled' => true]);

        $this->actingAsAdmin()
            ->get("profile")
            ->assertSee('Two-Factor Authentication');
    }

    /** @test */
    public function the_2fa_form_is_visible_on_edit_user_page_if_2fa_is_enabled()
    {
        config(['services.authy.key' => 'test']);

        $this->setSettings(['2fa.enabled' => false]);

        $user = UserFactory::create();

        $this->actingAsAdmin()
            ->get("/users/{$user->id}/edit")
            ->assertDontSee('Two-Factor Authentication');

        $this->setSettings(['2fa.enabled' => true]);

        $this->actingAsAdmin()
            ->get("/users/{$user->id}/edit")
            ->assertSee('Two-Factor Authentication');
    }

    /** @test */
    public function enable_2fa_from_profile_page()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $user = UserFactory::user()->create();

        Authy::shouldReceive('isEnabled')->andReturn(false);
        Authy::shouldReceive('register')->andReturnNull();
        Authy::shouldReceive('sendTwoFactorVerificationToken')->andReturnNull();

        $this->actingAs($user)
            ->post('/two-factor/enable', ['country_code' => '1', 'phone_number' => '123'])
            ->assertRedirect('/two-factor/verification');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'two_factor_country_code' => 1,
            'two_factor_phone' => 123
        ]);
    }

    /** @test */
    public function enable_2fa_from_edit_user_page()
    {
        $this->setSettings(['2fa.enabled' => true]);

        Authy::shouldReceive('isEnabled')->andReturn(false);
        Authy::shouldReceive('register')->andReturnNull();
        Authy::shouldReceive('sendTwoFactorVerificationToken')->andReturnNull();

        $user = UserFactory::user()->create();
        $formData = ['country_code' => '1', 'phone_number' => '123', 'user' => $user->id];

        $this->actingAsAdmin()
            ->post("users/{$user->id}/two-factor/enable", $formData)
            ->assertRedirect("two-factor/verification?user={$user->id}");

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'two_factor_country_code' => 1,
            'two_factor_phone' => 123
        ]);
    }

    /** @test */
    public function users_without_appropriate_permissions_cannot_enable_2fa_for_other_users()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->be(UserFactory::user()->create());

        Authy::shouldReceive('isEnabled')->andReturn(false);
        Authy::shouldReceive('register')->andReturnNull();
        Authy::shouldReceive('sendTwoFactorVerificationToken')->andReturnNull();

        $user = UserFactory::user()->create();

        $this->post('two-factor/enable', [
            'user' => $user->id,
            'country_code' => '1',
            'phone_number' => '123'
        ])->assertStatus(403);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'two_factor_country_code' => 1,
            'two_factor_phone' => 123
        ]);
    }

    /** @test */
    public function phone_verification_page_is_not_accessible_if_2fa_is_disabled_on_global_level()
    {
        $this->setSettings(['2fa.enabled' => false]);

        $this->actingAsAdmin()
            ->get("two-factor/verification")
            ->assertNotFound();
    }

    /** @test */
    public function phone_verification_page_is_not_accessible_if_user_phone_is_not_set()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $user1 = UserFactory::user()->twoFactor(null, null)->create();
        $user2 = UserFactory::user()->twoFactor(1, null)->create();
        $user3 = UserFactory::user()->twoFactor(null, '123456')->create();

        $this->actingAs($user1)->get("two-factor/verification")->assertNotFound();
        $this->actingAs($user2)->get("two-factor/verification")->assertNotFound();
        $this->actingAs($user3)->get("two-factor/verification")->assertNotFound();
    }

    /** @test */
    public function users_who_have_already_enabled_2fa_cannot_view_the_phone_verification_page()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->be(UserFactory::user()->create());

        Authy::shouldReceive('isEnabled')->andReturn(true);

        $this->get("two-factor/verification")->assertNotFound();
    }

    /** @test */
    public function users_who_have_already_enabled_2fa_cannot_submit_enable_2fa_form()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->be(UserFactory::user()->create());

        Authy::shouldReceive('isEnabled')->andReturn(true);

        $this->post("two-factor/enable", ['country_code' => '1', 'phone_number' => '123'])
            ->assertNotFound();
    }

    /** @test */
    public function users_who_have_already_enabled_2fa_cannot_submit_verification_form()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->be(UserFactory::user()->create());

        Authy::shouldReceive('isEnabled')->andReturn(true);

        $this->post("two-factor/verify")->assertNotFound();
    }

    /** @test */
    public function token_field_is_required_during_2fa_phone_verification()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $user = UserFactory::user()->twoFactor('1', '123456')->create();

        $this->actingAs($user)
            ->post("two-factor/verify")
            ->assertSessionHasErrors('token');
    }

    /** @test */
    public function the_2fa_verification_with_wrong_token_will_fail()
    {
        $this->withoutExceptionHandling();
        $this->setSettings(['2fa.enabled' => true]);

        $user = UserFactory::user()->twoFactor("1", "123123")->create();

        Authy::shouldReceive('isEnabled')->andReturn(false);
        Authy::shouldReceive('tokenIsValid')->with($user, "123123")->andReturn(false);

        $this->actingAs($user)
            ->post("two-factor/verify", ['token' => '123123']);

        $this->assertSessionHasError('Invalid 2FA token.');
    }

    /** @test */
    public function successful_2fa_phone_verification()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->expectsEvents(TwoFactorEnabled::class);

        $user = UserFactory::user()->twoFactor("1", "123123")->create();

        Authy::shouldReceive('isEnabled')->andReturn(false);
        Authy::shouldReceive('tokenIsValid')->with($user, '123123')->andReturn(true);

        $this->actingAs($user)
            ->post("two-factor/verify", ['token' => '123123'])
            ->assertRedirect("/profile");

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'two_factor_options' => '{"enabled":true}'
        ]);
    }

    /** @test */
    public function successful_2fa_phone_verification_for_other_user()
    {
        $this->withoutExceptionHandling();

        $this->setSettings(['2fa.enabled' => true]);

        $this->expectsEvents(TwoFactorEnabledByAdmin::class);

        $user = UserFactory::user()->twoFactor("1", "123123")->create();

        Authy::shouldReceive('isEnabled')->andReturn(false);
        Authy::shouldReceive('tokenIsValid')->once()->andReturn(true);

        $this->actingAsAdmin()
            ->post("two-factor/verify", ['token' => '123123', 'user' => $user->id])
            ->assertRedirect("/users/{$user->id}/edit");

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'two_factor_options' => '{"enabled":true}'
        ]);
    }

    /** @test */
    public function user_cannot_submit_phone_verification_form_if_phone_is_not_provided()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $user1 = UserFactory::user()->twoFactor(null, null)->create();
        $user2 = UserFactory::user()->twoFactor(1, null)->create();
        $user3 = UserFactory::user()->twoFactor(null, '123456')->create();

        $this->actingAs($user1)->post("two-factor/verify")->assertNotFound();
        $this->actingAs($user2)->post("two-factor/verify")->assertNotFound();
        $this->actingAs($user3)->post("two-factor/verify")->assertNotFound();
    }

    /** @test */
    public function user_can_request_a_new_sms_with_a_code_once_per_minute()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $user = UserFactory::user()->twoFactor("1", "123123")->create();

        $this->be($user);

        Authy::shouldReceive('isEnabled')->andReturn(false);
        Authy::shouldReceive('sendTwoFactorVerificationToken')->once()->andReturn(false);

        $this->post("/two-factor/resend");
        $this->post("/two-factor/resend");
        $this->post("/two-factor/resend");
    }

    /** @test */
    public function only_user_with_appropriate_permissions_can_request_new_2fa_token_for_another_user()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->be(UserFactory::user()->create());

        $user = UserFactory::user()->twoFactor("1", "123123")->create();

        $this->post("/two-factor/resend", ['user' => $user->id])
            ->assertStatus(403);
    }

    /** @test */
    public function user_can_request_a_new_sms_with_a_code_once_per_minute_while_enabling_2fa_for_other_user()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->beAdmin();

        $user = UserFactory::user()->twoFactor("1", "123123")->create();

        $repo = \Mockery::mock(UserRepository::class);
        $repo->shouldReceive('find')->with($user->id)->andReturn($user);
        $this->app->instance(UserRepository::class, $repo);

        Authy::shouldReceive('isEnabled')->andReturn(false);
        Authy::shouldReceive('sendTwoFactorVerificationToken')->once()->with($user)->andReturn(false);

        $this->post("/two-factor/resend", ['user' => $user->id]);
        $this->post("/two-factor/resend", ['user' => $user->id]);
        $this->post("/two-factor/resend", ['user' => $user->id]);
    }

    /** @test */
    public function users_cannot_request_new_codes_if_they_already_have_2fa_enabled()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->be(UserFactory::user()->create());

        Authy::shouldReceive('isEnabled')->andReturn(true);
        Authy::shouldReceive('sendTwoFactorVerificationToken')->never();

        $this->post("/two-factor/resend")->assertNotFound();
    }

    /** @test */
    public function user_cannot_hit_resend_endpoint_if_phone_is_not_provided()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $user1 = UserFactory::user()->twoFactor(null, null)->create();
        $user2 = UserFactory::user()->twoFactor(1, null)->create();
        $user3 = UserFactory::user()->twoFactor(null, '123456')->create();

        $this->actingAs($user1)->post("/two-factor/resend")->assertNotFound();
        $this->actingAs($user2)->post("/two-factor/resend")->assertNotFound();
        $this->actingAs($user3)->post("/two-factor/resend")->assertNotFound();
    }

    /** @test */
    public function user_can_disable_2fa()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->be(UserFactory::user()->create());

        $this->expectsEvents(\Vanguard\Events\User\TwoFactorDisabled::class);

        Authy::shouldReceive('isEnabled')->andReturn(true);
        Authy::shouldReceive('delete')->andReturnNull();

        $this->from('/profile')
            ->post('two-factor/disable')
            ->assertRedirect("/profile");

        $this->assertSessionHasSuccess('Two-Factor Authentication disabled successfully.');
    }

    /** @test */
    public function user_can_disable_2fa_for_another_user()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->expectsEvents(\Vanguard\Events\User\TwoFactorDisabled::class);

        $this->beAdmin();

        $user = UserFactory::user()->create();

        Authy::shouldReceive('isEnabled')->andReturn(true);
        Authy::shouldReceive('delete')->andReturnNull();

        $this->from("/users/{$user->id}/edit")
            ->post("users/{$user->id}/two-factor/disable")
            ->assertRedirect("/users/{$user->id}/edit");

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'two_factor_country_code' => null,
            'two_factor_phone' => null
        ]);

        $this->assertSessionHasSuccess('Two-Factor Authentication disabled successfully.');
    }

    /** @test */
    public function user_without_appropriate_permissions_cannot_disable_2fa_for_another_user()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->be(UserFactory::user()->create());

        $user = factory(User::class)->create();

        Authy::shouldReceive('isEnabled')->andReturn(true);

        $this->post("two-factor/disable", ['user' => $user->id])->assertForbidden();
    }
}
