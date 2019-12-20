<?php

namespace Tests\Feature\Http\Controllers\Api\Auth\Password;

use Mail;
use Tests\Feature\ApiTestCase;
use Vanguard\Mail\ResetPassword;
use Vanguard\User;

class RemindControllerTest extends ApiTestCase
{
    /** @test */
    public function send_password_reminder()
    {
        $this->setSettings(['forgot_password' => true]);

        Mail::fake();

        $user = factory(User::class)->create(['email' => 'test@test.com']);

        $this->postJson('api/password/remind', ['email' => 'test@test.com'])
            ->assertOk();

        Mail::assertQueued(ResetPassword::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    /** @test */
    public function password_reminder_with_wrong_email()
    {
        $this->setSettings(['forgot_password' => true]);

        $this->postJson('api/password/remind', ['email' => 'test@test.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }
}
