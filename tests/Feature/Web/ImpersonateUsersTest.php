<?php

namespace Tests\Feature\Web;

use Facades\Tests\Setup\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Vanguard\User;

class ImpersonateUsersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed --class=RolesSeeder');
        $this->artisan('db:seed --class=PermissionsSeeder');
    }

    /** @test */
    public function unverified_users_cannot_impersonate_other_users()
    {
        $user = UserFactory::withPermissions('users.manage')->unverified()->create();

        factory(User::class)->create();

        $this->actingAs($user)->get('/users')->assertRedirect("/email/verify");
    }

    /** @test */
    public function a_user_with_appropriate_permission_can_impersonate_other_users_from_a_user_list_page()
    {
        $user = UserFactory::withPermissions('users.manage')->create();

        factory(User::class)->create();

        $this->actingAs($user)->get('/users')->assertSee("Impersonate");
    }

    /** @test */
    public function a_user_dont_see_impersonate_button_next_to_his_name_in_the_user_list()
    {
        $user = UserFactory::withPermissions('users.manage')->create();

        $this->actingAs($user)->get('/user')->assertDontSee("Impersonate");
    }

    /** @test */
    public function clicking_on_impersonate_button_will_impersonate_the_user()
    {
        $userA = UserFactory::withPermissions('users.manage')->create();
        $userB = UserFactory::user()->create();

        $this->actingAs($userA)
            ->get(route('impersonate', $userB))
            ->assertRedirect("/");

        $this->assertTrue(auth()->user()->is($userB));
    }

    /** @test */
    public function while_impersonating_user_can_stop_impersonating_by_clicking_on_the_header_button()
    {
        $userA = UserFactory::withPermissions('users.manage')->create();
        $userB = UserFactory::user()->create();

        $this->actingAs($userA)->get(route('impersonate', $userB));

        $this->assertTrue(auth()->user()->is($userB));

        $this->get("/")->assertSee("Stop Impersonating");

        $this->get(route('impersonate.leave'));

        $this->assertTrue(auth()->user()->is($userA));
    }

    /** @test */
    public function while_impersonating_user_cannot_impersonate_other_user_even_if_he_has_a_permission()
    {
        $userA = UserFactory::withPermissions('users.manage')->create();
        $userB = UserFactory::withPermissions('users.manage')->create();

        $this->actingAs($userA)->get(route('impersonate', $userB));

        $this->get("/user")
            ->assertDontSee("Impersonate");
    }
}
