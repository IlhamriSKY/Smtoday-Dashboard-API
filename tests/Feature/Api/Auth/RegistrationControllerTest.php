<?php

namespace Tests\Feature\Http\Controllers\Api\Auth;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\ApiTestCase;
use Vanguard\User;

class RegistrationControllerTest extends ApiTestCase
{
    /** @test */
    public function register_user_when_registration_is_disabled()
    {
        $this->setSettings(['reg_enabled' => false]);

        $this->postJson('api/register')->assertStatus(404);
    }

    /** @test */
    public function register_user()
    {
        $this->setSettings([
            'reg_enabled' => true,
            'reg_email_confirmation' => false,
            'registration.captcha.enabled' => false,
            'tos' => false
        ]);

        $data = [
            'email' => 'john.doe@test.com',
            'username' => 'john.doe',
            'password' => '123123123',
            'password_confirmation' => '123123123'
        ];

        $expected = Arr::except($data, ['password', 'password_confirmation']);

        $this->postJson("/api/register", $data)
            ->assertStatus(201)
            ->assertJson([
                'requires_email_confirmation' => false
            ]);

        $this->assertDatabaseHas('users', $expected);
    }

    /** @test */
    public function register_user_with_email_confirmation()
    {
        $this->setSettings([
            'reg_enabled' => true,
            'reg_email_confirmation' => true,
            'registration.captcha.enabled' => false,
            'tos' => false
        ]);

        Notification::fake();

        $data = [
            'email' => 'john.doe@test.com',
            'username' => 'john.doe',
            'password' => '123123123',
            'password_confirmation' => '123123123'
        ];

        $expected = Arr::except($data, ['password', 'password_confirmation']);

        $this->postJson("/api/register", $data)
            ->assertStatus(201)
            ->assertJson(['requires_email_confirmation' => true]);

        $this->assertDatabaseHas('users', $expected);

        $user = User::where('email', $data['email'])->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** @test */
    public function register_with_tos()
    {
        $this->setSettings([
            'reg_enabled' => true,
            'reg_email_confirmation' => false,
            'registration.captcha.enabled' => false,
            'tos' => true
        ]);

        $data = [
            'email' => 'john.doe@test.com',
            'username' => 'john.doe',
            'password' => '123123123',
            'password_confirmation' => '123123123'
        ];

        $this->postJson("/api/register", $data)
            ->assertStatus(422)
            ->assertJsonFragment([
                'tos' => ["You have to accept Terms of Service."]
            ]);
    }
}
