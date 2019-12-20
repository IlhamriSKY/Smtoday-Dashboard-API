<?php

namespace Tests\Feature\Http\Controllers\Web;

use Carbon\Carbon;
use Facades\Tests\Setup\UserFactory;
use Hash;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Storage;
use Facades\Tests\Setup\RoleFactory;
use Tests\TestCase;
use Tests\UpdatesSettings;
use Vanguard\Events\User\UpdatedByAdmin;
use Vanguard\Role;
use Vanguard\Services\Auth\Api\Token;
use Vanguard\Support\Enum\UserStatus;
use Vanguard\User;

class UsersTest extends TestCase
{
    use RefreshDatabase, UpdatesSettings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolesSeeder']);
        $this->artisan('db:seed', ['--class' => 'PermissionsSeeder']);
        $this->artisan('db:seed', ['--class' => 'CountriesSeeder']);
    }

    /** @test */
    public function guests_cannot_view_the_user_list_page()
    {
        $this->get('/users')->assertRedirect('/login');
    }

    /** @test */
    public function users_without_appropriate_permission_cannot_view_user_list_page()
    {
        $roleA = RoleFactory::create();
        $roleB = RoleFactory::withPermissions('users.manage')->create();

        $userA = UserFactory::role($roleA)->create();
        $userB = UserFactory::role($roleB)->create();

        $this->actingAs($userA)->get('/users')->assertForbidden();
        $this->actingAs($userB)->get('/users')->assertOk();
    }

    /** @test */
    public function user_collection_is_being_properly_passed_to_the_view()
    {
        $admin = UserFactory::admin()->create();
        $active = UserFactory::user()->create();
        $banned = UserFactory::user()->banned()->create();
        $unconfirmed = UserFactory::user()->unconfirmed()->create();

        $users = $this->actingAs($admin)->get('users')->viewData('users');

        $this->assertCount(4, $users);
        $this->assertTrue($users->contains($admin));
        $this->assertTrue($users->contains($active));
        $this->assertTrue($users->contains($banned));
        $this->assertTrue($users->contains($unconfirmed));
    }

    /** @test */
    public function user_list_is_paginated()
    {
        $this->beAdmin();

        $users = $this->get('users')->viewData('users');

        $this->assertInstanceOf(Paginator::class, $users);
        $this->assertCount(1, $users->items());
    }

    /** @test */
    public function users_can_be_filtered_out_by_search_term()
    {
        $user1 = factory(User::class)->create(['first_name' => 'Milos', 'last_name' => 'Stojanovic']);
        $user2 = factory(User::class)->create(['first_name' => 'John', 'last_name' => 'Doe']);
        $user3 = factory(User::class)->create(['first_name' => 'Jane', 'last_name' => 'Doe']);

        $users = $this->actingAsAdmin()->get('users?search=doe')->viewData('users');

        $this->assertCount(2, $users);
        $this->assertTrue($users->contains($user3));
        $this->assertTrue($users->contains($user2));
    }

    /** @test */
    public function users_can_be_filtered_out_by_status()
    {
        factory(User::class)->times(2)->create();
        factory(User::class)->times(3)->create(['status' => UserStatus::UNCONFIRMED]);

        $users = $this->actingAsAdmin()
            ->get('users?status=' . UserStatus::BANNED)
            ->viewData('users');

        $this->assertCount(0, $users);
    }

    /** @test */
    public function admin_can_successfully_create_new_users()
    {
        $data = $this->validParams();

        $this->actingAsAdmin()
            ->post('/users', $data)
            ->assertRedirect('/users');

        $this->assertSessionHasSuccess('User created successfully.');

        $user = User::where('email', $data['email'])->first();

        $this->assertDatabaseHas('users', Arr::except($data, ['password', 'password_confirmation']));
        $this->assertTrue(Hash::check('123123', $user->password));
    }

    /** @test */
    public function users_without_appropriate_permissions_cannot_create_new_users()
    {
        $roleA = RoleFactory::create();
        $roleB = RoleFactory::withPermissions('users.manage')->create();

        $userA = UserFactory::role($roleA)->create();
        $userB = UserFactory::role($roleB)->create();

        $data = $this->validParams();

        $this->actingAs($userA)->post('/users', $data)->assertForbidden();
        $this->assertNUll(User::where('email', $data['email'])->first());

        $this->actingAs($userB)->post('/users', $data)->assertRedirect('/users');
        $this->assertNotNull(User::where('email', $data['email'])->first());
    }

    /** @test */
    public function email_field_is_required_while_creating_a_user()
    {
        $data = Arr::except($this->validParams(), ['email']);

        $this->actingAsAdmin()
            ->from('/users')
            ->post('/users', $data)
            ->assertRedirect('/users')
            ->assertSessionHasErrors('email');
    }

    /** @test */
    public function email_field_must_be_a_valid_email_while_creating_a_user()
    {
        $data = $this->validParams(['email' => 'foo']);

        $this->actingAsAdmin()
            ->from('/users')
            ->post('/users', $data)
            ->assertRedirect('/users')
            ->assertSessionHasErrors('email');
    }

    /** @test */
    public function email_must_be_unique_while_creating_a_user()
    {
        $this->beAdmin();

        UserFactory::user()->email('john@example.com')->create();

        $data = $this->validParams(['email' => 'john@example.com']);

        $this->from('/users')
            ->post('/users', $data)
            ->assertRedirect('/users')
            ->assertSessionHasErrors('email');
    }

    /** @test */
    public function username_is_not_required()
    {
        $this->beAdmin();

        $data = $this->validParams();
        unset($data['username']);

        $this->from('/users')
            ->post('/users', $data)
            ->assertRedirect('/users');

        $this->assertNotNull(User::where('email', $data['email'])->first());
    }

    /** @test */
    public function username_must_be_unique_while_creating_a_user()
    {
        $this->beAdmin();

        factory(User::class)->create(['username' => 'johndoe']);

        $data = $this->validParams(['username' => 'johndoe']);

        $this->from('/users')
            ->post('/users', $data)
            ->assertRedirect('/users')
            ->assertSessionHasErrors('username');
    }

    /** @test */
    public function password_is_required_while_creating_a_user()
    {
        $this->beAdmin();

        $data = $this->validParams();
        unset($data['password']);

        $this->from('/users')
            ->post('/users', $data)
            ->assertRedirect('/users')
            ->assertSessionHasErrors('password');
    }

    /** @test */
    public function password_must_be_at_least_6_characters_long_while_creating_a_user()
    {
        $this->beAdmin();

        $data = $this->validParams(['password' => '12345']);

        $this->from('/users')
            ->post('/users', $data)
            ->assertRedirect('/users')
            ->assertSessionHasErrors('password');
    }

    /** @test */
    public function password_must_be_confirmed_while_creating_a_user()
    {
        $this->beAdmin();

        $data = $this->validParams();
        unset($data['password_confirmation']);

        $this->from('/users')
            ->post('/users', $data)
            ->assertRedirect('/users')
            ->assertSessionHasErrors('password');
    }

    /** @test */
    public function birthday_must_be_a_valid_date_while_creating_a_user()
    {
        $this->beAdmin();

        $data = $this->validParams(['birthday' => 'foo']);

        $this->from('/users')
            ->post('/users', $data)
            ->assertRedirect('/users')
            ->assertSessionHasErrors('birthday');
    }

    /** @test */
    public function role_field_is_required_while_creating_a_user()
    {
        $this->beAdmin();

        $data = $this->validParams();
        unset($data['role_id']);

        $this->from('/users')
            ->post('/users', $data)
            ->assertRedirect('/users')
            ->assertSessionHasErrors('role_id');
    }

    /** @test */
    public function selected_role_must_exist_inside_the_system_while_creating_a_user()
    {
        $this->beAdmin();

        $data = $this->validParams(['role_id' => 123]);

        $this->from('/users')
            ->post('/users', $data)
            ->assertRedirect('/users')
            ->assertSessionHasErrors('role_id');
    }

    /** @test */
    public function country_id_field_is_not_required_while_creating_a_user()
    {
        $data = $this->validParams(['country_id' => 0]);

        $this->actingAsAdmin()
            ->post('/users', $data)
            ->assertRedirect('/users');

        $this->assertSessionHasSuccess('User created successfully.');

        $user = User::where('email', $data['email'])->first();

        $expected = Arr::except($data, ['password', 'password_confirmation']);
        $expected['country_id'] = null;

        $this->assertDatabaseHas('users', $expected);
        $this->assertTrue(Hash::check('123123', $user->password));
    }

    /** @test */
    public function country_id_must_exist_inside_the_system_while_creating_a_user()
    {
        $this->beAdmin();

        $data = $this->validParams(['country_id' => 12345]);

        $this->from('/users')
            ->post('/users', $data)
            ->assertRedirect('/users')
            ->assertSessionHasErrors('country_id');
    }

    /** @test */
    public function admin_can_view_users_profile()
    {
        $user = UserFactory::user()->create();

        $this->actingAsAdmin()->get("users/{$user->id}")->assertOk();
    }

    /** @test */
    public function users_without_appropriate_permissions_cannot_view_profile_for_another_user()
    {
        $roleA = RoleFactory::create();
        $roleB = RoleFactory::withPermissions('users.manage')->create();

        $userA = UserFactory::role($roleA)->create();
        $userB = UserFactory::role($roleB)->create();

        $this->actingAs($userA)->get("users/{$userB->id}")->assertForbidden();
        $this->actingAs($userB)->get("users/{$userA->id}")->assertOk();
    }

    /** @test */
    public function update_user_details()
    {
        $this->expectsEvents(UpdatedByAdmin::class);

        $this->beAdmin();

        $user = UserFactory::user()->create();

        $data = $this->validParams();

        $this->from("users/{$user->id}/edit")
            ->put("/users/{$user->id}/update/details", $data)
            ->assertRedirect("users/{$user->id}/edit");

        $expected = Arr::except($data, ['password', 'password_confirmation']);

        $this->assertDatabaseHas('users', $expected + ['id' => $user->id]);
        $this->assertSessionHasSuccess('User updated successfully.');
    }

    /** @test */
    public function users_without_appropriate_permissions_cannot_update_other_users()
    {
        $roleA = RoleFactory::create();
        $roleB = RoleFactory::withPermissions('users.manage')->create();

        $userA = UserFactory::role($roleA)->create();
        $userB = UserFactory::role($roleB)->create();

        $data = $this->validParams();

        $this->actingAs($userA)
            ->from("users/{$userB->id}/edit")
            ->put("/users/{$userB->id}/update/details", $data)
            ->assertForbidden();

        $this->assertNotEquals($data['email'], $userB->fresh()->email);

        $this->actingAs($userB)
            ->from("users/{$userA->id}/edit")
            ->put("/users/{$userA->id}/update/details", $data)
            ->assertRedirect("users/{$userA->id}/edit");

        $this->assertEquals($data['email'], $userA->fresh()->email);
    }

    /** @test */
    public function banning_a_user_will_invalidate_all_his_sessions_and_api_tokens()
    {
        config(['session.driver' => 'database']);

        $this->beAdmin();

        $user = UserFactory::user()->create(['remember_token' => Str::random(60)]);

        \DB::table('sessions')->insert([
            'id' => Str::random(40),
            'user_id' => $user->id,
            'ip_address' => "127.0.0.1",
            'user_agent' => 'Foo',
            'payload' => Str::random(),
            'last_activity' => Carbon::now()->subMinute()->timestamp
        ]);

        factory(Token::class)->create(['user_id' => $user->id]);

        $data = $this->validParams(['status' => UserStatus::BANNED]);

        $this->put("/users/{$user->id}/update/details", $data);

        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
        $this->assertNull($user->fresh()->remember_token);
        $this->assertDatabaseMissing('api_tokens', ['user_id' => $user->id]);
    }

    /** @test */
    public function admin_can_update_users_login_details()
    {
        $this->beAdmin();

        $user = UserFactory::user()->create();

        $data = [
            'email' => 'john@doe.com',
            'username' => 'milos',
            'password' => '123123123',
            'password_confirmation' => '123123123'
        ];

        $this->put("users/{$user->id}/update/login-details", $data)
            ->assertRedirect("users/{$user->id}/edit");

        $this->assertSessionHasSuccess('Login details updated successfully.');

        $user = $user->fresh();

        $this->assertEquals($data['email'], $user->email);
        $this->assertEquals($data['username'], $user->username);
        $this->assertTrue(Hash::check($data['password'], $user->password));
    }

    /** @test */
    public function users_without_appropriate_permissions_cannot_update_other_users_login_details()
    {
        $roleA = RoleFactory::create();
        $roleB = RoleFactory::withPermissions('users.manage')->create();

        $userA = UserFactory::role($roleA)->create();
        $userB = UserFactory::role($roleB)->create();

        $data = [
            'email' => 'john@doe.com',
            'username' => 'milos',
            'password' => '123123123',
            'password_confirmation' => '123123123'
        ];

        $this->actingAs($userA)
            ->from("users/{$userB->id}/edit")
            ->put("users/{$userB->id}/update/login-details", $data)
            ->assertForbidden();

        $this->assertNotEquals($data['email'], $userB->fresh()->email);

        $this->actingAs($userB)
            ->from("users/{$userA->id}/edit")
            ->put("users/{$userA->id}/update/login-details", $data)
            ->assertRedirect("users/{$userA->id}/edit");

        $this->assertEquals($data['email'], $userA->fresh()->email);
    }

    /** @test */
    public function two_factor_form_visibility()
    {
        config(['services.authy.key' => 'test']);

        $this->setSettings(['2fa.enabled' => false]);

        $user = UserFactory::user()->create();

        $this->actingAsAdmin()
            ->get("users/{$user->id}/edit")
            ->assertDontSee('Two-Factor Authentication');

        $this->setSettings(['2fa.enabled' => true]);

        $this->actingAsAdmin()
            ->get("users/{$user->id}/edit")
            ->assertSee('Two-Factor Authentication');
    }

    /** @test */
    public function admin_can_update_avatar_on_behalf_of_a_user()
    {
        Storage::fake('public');

        $data = [
            'avatar' => UploadedFile::fake()->image('photo1.jpg', 300, 300),
            'points' => [
                'x1' => 0,
                'y1' => 0,
                'x2' => 200,
                'y2' => 200
            ]
        ];

        $user = UserFactory::user()->create();

        $this->actingAsAdmin()
            ->from("users/{$user->id}/edit")
            ->post("users/{$user->id}/update/avatar", $data)
            ->assertRedirect("users/{$user->id}/edit");

        $this->assertSessionHasSuccess('Avatar changed successfully.');

        $user->refresh();

        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists("upload/users/{$user->avatar}");

        list($width, $height) = getimagesizefromstring(
            Storage::disk('public')->get("upload/users/{$user->avatar}")
        );

        $this->assertEquals(160, $width);
        $this->assertEquals(160, $height);
    }

    /** @test */
    public function admin_can_update_avatar_on_behalf_of_a_user_only_if_valid_file_is_selected()
    {
        Storage::fake('public');

        $data = [
            'avatar' => UploadedFile::fake()->create('foo.txt', 123),
            'points' => [
                'x1' => 0,
                'y1' => 0,
                'x2' => 200,
                'y2' => 200
            ]
        ];

        $user = UserFactory::user()->create();

        $this->actingAsAdmin()
            ->from("users/{$user->id}/edit")
            ->post("users/{$user->id}/update/avatar", $data)
            ->assertRedirect("users/{$user->id}/edit");

        $this->assertNull($user->fresh()->avatar);
    }

    /** @test */
    public function users_without_appropriate_permissions_cannot_update_avatar_for_other_users()
    {
        Storage::fake('public');

        $roleA = RoleFactory::create();
        $roleB = RoleFactory::withPermissions('users.manage')->create();

        $userA = UserFactory::role($roleA)->create();
        $userB = UserFactory::role($roleB)->create();

        $data = [
            'avatar' => UploadedFile::fake()->image('photo1.jpg', 300, 300),
            'points' => [
                'x1' => 0,
                'y1' => 0,
                'x2' => 200,
                'y2' => 200
            ]
        ];

        $this->actingAs($userA)
            ->from("users/{$userB->id}/edit")
            ->post("users/{$userB->id}/update/avatar", $data)
            ->assertForbidden();

        $this->assertNull($userB->fresh()->avatar);

        $this->actingAs($userB)
            ->from("users/{$userA->id}/edit")
            ->post("users/{$userA->id}/update/avatar", $data)
            ->assertRedirect("users/{$userA->id}/edit");

        $this->assertNotNull($userA->fresh()->avatar);
    }

    /** @test */
    public function session_page_is_not_available_for_non_database_driver()
    {
        config(['session.driver' => 'array']);

        $user = UserFactory::admin()->create();

        $this->actingAs($user)
            ->get('users')
            ->assertDontSee('User Sessions');

        // this page should not be accessible if
        // database session driver is not being used
        $this->get("users/{$user->id}/sessions")->assertNotFound();
    }

    /** @test */
    public function invalidate_session()
    {
        config(['session.driver' => 'database']);

        Carbon::setTestNow(Carbon::now());

        $user = UserFactory::admin()->withCredentials('foo', 'bar')->create();

        $agent = $this->app['agent'];
        $device = $agent->device() ?: 'Unknown';
        $platform = $agent->platform() ?: 'Unknown';

        // Log-in manually to actually create session record in DB
        $this->post('/login', ['username' => 'foo', 'password' => 'bar']);

        $this->get("users/{$user->id}/sessions")
            ->assertSee('127.0.0.1')
            ->assertSee($device)
            ->assertSee($platform)
            ->assertSee($agent->browser());

        $sessionId = \DB::table('sessions')->where('user_id', $user->id)->first()->id;
        $this->delete("users/{$user->id}/sessions/{$sessionId}/invalidate");

        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
    }

    /** @test */
    public function admins_can_delete_other_users()
    {
        $user = UserFactory::user()->create();

        $this->actingAsAdmin()
            ->delete(route('users.destroy', $user))
            ->assertRedirect('users');

        $this->assertSessionHasSuccess("User deleted successfully.");

        $this->assertNull($user->fresh());
    }

    /** @test */
    public function users_without_appropriate_permissions_cannot_delete_other_users()
    {
        $roleA = RoleFactory::create();
        $roleB = RoleFactory::withPermissions('users.manage')->create();

        $userA = UserFactory::role($roleA)->create();
        $userB = UserFactory::role($roleB)->create();

        $this->actingAs($userA)
            ->delete(route('users.destroy', $userB))
            ->assertForbidden();

        $this->assertNotNull($userB->fresh());

        $this->actingAs($userB)
            ->delete(route('users.destroy', $userA))
            ->assertRedirect('/users');

        $this->assertNull($userA->fresh());
    }

    /** @test */
    public function user_cannot_delete_himself()
    {
        $this->beAdmin();

        $this->delete(route('users.destroy', auth()->id()))
            ->assertRedirect('/users');

        $this->assertNotNull(auth()->user()->fresh());
        $this->assertSessionHasError("You cannot delete yourself.");
    }

    protected function validParams(array $overrides = [])
    {
        return array_merge([
            'role_id' => Role::whereName('User')->first()->id,
            'status' => UserStatus::ACTIVE,
            'first_name' => 'foo',
            'last_name' => 'bar',
            'birthday' => Carbon::now()->subYears(25)->format('Y-m-d'),
            'phone' => '12345667',
            'address' => 'the address',
            'country_id' => 688, //Serbia,
            'email' => 'john@doe.com',
            'username' => 'johndoe',
            'password' => '123123',
            'password_confirmation' => '123123'
        ], $overrides);
    }
}
