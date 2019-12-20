<?php

namespace Tests\Feature\Http\Controllers\Api\Users;

use Authy;
use Facades\Tests\Setup\UserFactory;
use Mockery;
use Tests\Feature\ApiTestCase;
use Vanguard\Events\User\TwoFactorDisabledByAdmin;
use Vanguard\Events\User\TwoFactorEnabledByAdmin;
use Vanguard\Transformers\UserTransformer;
use Vanguard\User;

class TwoFactorControllerTest extends ApiTestCase
{
    /** @test */
    public function update_2fa_unathenticated()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $user = factory(User::class)->create();

        $this->putJson("api/users/{$user->id}/2fa")
            ->assertStatus(401);
    }

    /** @test */
    public function update_2fa_without_permission()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $user = factory(User::class)->create();

        $this->actingAs($user, 'api')
            ->putJson("api/users/{$user->id}/2fa")
            ->assertForbidden();
    }

    /** @test */
    public function enable_two_factor_auth_for_user()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->doesntExpectEvents(TwoFactorEnabledByAdmin::class);

        $user = UserFactory::withPermissions('users.manage')->create();

        Authy::shouldReceive('isEnabled')->andReturn(false);
        Authy::shouldReceive('register')->andReturnNull();
        Authy::shouldReceive('sendTwoFactorVerificationToken');

        $data = ['country_code' => '1', 'phone_number' => '123'];
 
        $this->actingAs($user, 'api')->putJson("api/users/{$user->id}/2fa", $data)
            ->assertOk()
            ->assertJson(['message' => 'Verification token sent.']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'two_factor_country_code' => $data['country_code'],
            'two_factor_phone' => $data['phone_number']
        ]);
    }

    /** @test */
    public function verify_user_phone_with_correct_token()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->expectsEvents(TwoFactorEnabledByAdmin::class);

        $user = UserFactory::withPermissions('users.manage')->create();

        Authy::shouldReceive('isEnabled')->andReturn(false);
        Authy::shouldReceive('tokenIsValid')->with(Mockery::any(), '123123')->andReturn(true);

        $response = $this->actingAs($user, 'api')
            ->postJson("api/users/{$user->id}/2fa/verify", ['token' => '123123']);

        $transformer = new UserTransformer;
        $updatedUser = $transformer->transform($user->fresh());

        $response->assertOk()
            ->assertJson($updatedUser);

        $this->assertTrue($user->fresh()->getTwoFactorAuthProviderOptions()['enabled']);
    }

    /** @test */
    public function verify_user_phone_with_invalid_token()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $user = UserFactory::withPermissions('users.manage')->create();

        Authy::shouldReceive('isEnabled')->andReturn(false);
        Authy::shouldReceive('tokenIsValid')->andReturn(false);

        $this->actingAs($user, 'api')
            ->postJson("api/users/{$user->id}/2fa/verify", ['token' => '123123'])
            ->assertStatus(422)
            ->assertJson([
                'error' => 'Invalid 2FA token.'
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'two_factor_options' => '{"enabled":true}'
        ]);
    }

    /** @test */
    public function enable_two_factor_auth_for_user_when_it_is_already_enabled()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $user = UserFactory::withPermissions('users.manage')->create();

        Authy::shouldReceive('isEnabled')->andReturn(true);

        $data = ['country_code' => '1', 'phone_number' => '123'];

        $this->actingAs($user, 'api')
            ->putJson("api/users/{$user->id}/2fa", $data)
            ->assertStatus(422)
            ->assertJson([
                'error' => '2FA is already enabled for this user.'
            ]);
    }

    /** @test */
    public function disable_two_factor_auth_for_user()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->expectsEvents(TwoFactorDisabledByAdmin::class);

        $user = UserFactory::withPermissions('users.manage')->twoFactor('1', '123')->create();

        $this->be($user, 'api');

        Authy::shouldReceive('isEnabled')->andReturn(true);
        Authy::shouldReceive('delete')->andReturnNull();

        $response = $this->deleteJson("api/users/{$user->id}/2fa");

        $transformer = new UserTransformer;
        $user = $transformer->transform($user->fresh());

        $response->assertOk()
            ->assertJson($user);
    }

    /** @test */
    public function disable_2fa_for_user_when_it_is_already_disabled()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $user = UserFactory::withPermissions('users.manage')->create();

        Authy::shouldReceive('isEnabled')->andReturn(false);

        $this->actingAs($user, 'api')
            ->deleteJson("api/users/{$user->id}/2fa")
            ->assertStatus(422)
            ->assertJson([
                'error' => '2FA is not enabled for this user.'
            ]);
    }
}
