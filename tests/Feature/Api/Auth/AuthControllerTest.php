<?php

namespace Tests\Feature\Http\Controllers\Api\Auth;

use Carbon\Carbon;
use Tests\Feature\ApiTestCase;
use Vanguard\Services\Auth\Api\Token;
use Vanguard\Support\Enum\UserStatus;
use Vanguard\User;

class AuthControllerTest extends ApiTestCase
{
    /** @test */
    public function login()
    {
        $credentials = [
            'username' => 'foo',
            'password' => 'bar'
        ];

        $user = factory(User::class)->create($credentials);

        $response = $this->postJson("/api/login", $credentials)->assertOk();

        $token = Token::where('user_id', $user->id)->first();

        $this->assertJwtTokenContains($response, $token->id);
    }

    /** @test */
    public function last_login_timestamp_is_updated_after_login()
    {
        $credentials = [
            'username' => 'foo',
            'password' => 'bar'
        ];

        $now = Carbon::now();

        Carbon::setTestNow($now);

        $user = factory(User::class)->create($credentials);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'last_login' => null
        ]);

        $this->postJson("/api/login", $credentials)->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'last_login' => $now
        ]);
    }

    /** @test */
    public function login_with_invalid_credentials()
    {
        $credentials = [
            'username' => 'foo',
            'password' => 'bar'
        ];

        factory(User::class)->create($credentials);

        $this->postJson("/api/login", [
            'username' => 'foo',
            'password' => 'invalid'
        ])->assertStatus(401)
            ->assertJson(['error' => "Invalid credentials."]);
    }

    /** @test */
    public function login_when_credentials_are_not_provided()
    {
        $this->postJson("/api/login")
            ->assertStatus(422)
            ->assertJsonFragment([
                'username' => [
                    trans('validation.required', ['attribute' => 'username'])
                ],
                'password' => [
                    trans('validation.required', ['attribute' => 'password'])
                ]
            ]);
    }

    /** @test */
    public function banned_user_cannot_log_in()
    {
        $this->withoutExceptionHandling();

        $credentials = [
            'username' => 'foo',
            'password' => 'bar'
        ];

        $user = factory(User::class)->create(array_merge($credentials, [
            'status' => UserStatus::BANNED
        ]));

        $this->postJson("/api/login", $credentials)
            ->assertStatus(401)
            ->assertJson([
                'error' => "Your account is banned by administrators."
            ]);

        $this->assertDatabaseMissing('api_tokens', ['user_id' => $user->id]);
    }

    /** @test */
    public function unconfirmed_user_cannot_log_in()
    {
        $credentials = [
            'username' => 'foo',
            'password' => 'bar'
        ];

        $user = factory(User::class)->create(array_merge($credentials, [
            'status' => UserStatus::UNCONFIRMED
        ]));

        $this->postJson("/api/login", $credentials)
            ->assertStatus(401)
            ->assertJson([
                'error' => "Please confirm your email address first."
            ]);

        $this->assertDatabaseMissing('api_tokens', ['user_id' => $user->id]);
    }

    /** @test */
    public function logout()
    {
        $credentials = [
            'username' => 'foo',
            'password' => 'bar'
        ];

        Carbon::setTestNow(Carbon::now());

        $user = factory(User::class)->create($credentials);

        $response = $this->postJson("/api/login", $credentials);

        $token = Token::where('user_id', $user->id)->first();

        $this->postJson("/api/logout", [], [
            'Authorization' => "Bearer {$response->original['token']}"
        ]);

        $this->assertDatabaseMissing('api_tokens', ['id' => $token->id])
            ->assertNull(auth('api')->user());
    }

    /** @test */
    public function logout_if_token_is_not_provided()
    {
        $this->postJson("/api/logout")->assertStatus(401);
    }
}
