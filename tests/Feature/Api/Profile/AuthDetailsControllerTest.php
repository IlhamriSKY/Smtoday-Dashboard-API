<?php

namespace Tests\Feature\Http\Controllers\Api\Profile;

use Facades\Tests\Setup\UserFactory;
use Tests\Feature\ApiTestCase;

class AuthDetailsControllerTest extends ApiTestCase
{
    /** @test */
    public function user_can_update_his_authentication_details()
    {
        $user = $this->login();

        $this->patch('/api/me/details/auth', [
            'email' => 'foo@example.com',
            'username' => 'john.doe',
            'password' => '12345678',
            'password_confirmation' => '12345678'
        ])->assertOk()
            ->assertJson(['email' => 'foo@example.com', 'username' => 'john.doe']);

        $this->assertTrue(password_verify('12345678', $user->fresh()->password));
    }

    /** @test */
    public function user_can_update_only_email_and_leave_other_fields_unchanged()
    {
        $user = $this->login();

        $this->patch('/api/me/details/auth', [
            'email' => 'foo@example.com',
        ])->assertOk()
            ->assertJson(['email' => 'foo@example.com']);

        $this->assertEquals($user->username, $user->fresh()->username);
        $this->assertEquals($user->password, $user->fresh()->password);
    }

    /** @test */
    public function email_field_is_required()
    {
        $this->login();

        $this->patch('/api/me/details/auth')
            ->assertJsonValidationErrors('email');
    }

    /** @test */
    public function email_field_must_be_valid_email()
    {
        $this->login();

        $this->patch('/api/me/details/auth', [
            'email' => 'invalid email'
        ])->assertJsonValidationErrors('email');
    }

    /** @test */
    public function email_field_must_be_unique()
    {
        $this->login();

        UserFactory::email('john.doe@test.com')->create();

        $this->patch('/api/me/details/auth', [
            'email' => 'john.doe@test.com',
        ])->assertJsonValidationErrors('email');
    }

    /** @test */
    public function username_field_must_be_unique()
    {
        $this->login();

        UserFactory::withCredentials('john.doe', '123123')->create();

        $this->patch('/api/me/details/auth', [
            'email' => 'john.doe@test.com',
            'username' => 'john.doe'
        ])->assertJsonValidationErrors('username');
    }
}
