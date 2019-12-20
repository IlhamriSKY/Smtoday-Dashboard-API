<?php

namespace Tests\Feature\Http\Controllers\Web;

use Carbon\Carbon;
use Facades\Tests\Setup\UserFactory;
use Hash;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Storage;
use Tests\TestCase;
use Vanguard\Events\User\ChangedAvatar;
use Vanguard\Events\User\UpdatedProfileDetails;
use Vanguard\Role;
use Vanguard\Support\Enum\UserStatus;
use Vanguard\UserActivity\Logger;

class UpdateProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->be(UserFactory::create());
    }

    /** @test */
    public function user_can_access_his_profile_page()
    {
        $this->get('/profile')->assertOk();
    }

    /** @test
     */
    public function user_can_update_his_profile_details()
    {
        $this->expectsEvents(UpdatedProfileDetails::class);

        $data = $this->getStubDetailsData();

        $this->from('/profile')
            ->put('profile/details', $data)
            ->assertRedirect('/profile');

        $this->assertSessionHasSuccess('Profile updated successfully.');
        $this->assertDatabaseHas('users', $data + ['id' => auth()->id()]);
    }

    /** @test */
    public function user_cannot_change_his_status_while_updating_the_profile()
    {
        $data = $this->getStubDetailsData();

        $this->from('/profile')
            ->put('profile/details', $data + ['status' => UserStatus::BANNED])
            ->assertRedirect('/profile');

        $this->assertSessionHasSuccess('Profile updated successfully.');
        $this->assertDatabaseHas('users', $data + [
            'id' => auth()->id(),
            'status' => UserStatus::ACTIVE,
        ]);
    }

    /** @test */
    public function user_cannot_change_his_role_while_updating_the_profile()
    {
        $data = $this->getStubDetailsData();

        $role = factory(Role::class)->create();

        $this->from('/profile')
            ->put('profile/details', $data + ['role_id' => $role])
            ->assertRedirect('/profile');

        $this->assertSessionHasSuccess('Profile updated successfully.');

        $this->assertNotEquals($role->id, auth()->user()->role_id);
    }

    /** @test */
    public function update_avatar()
    {
        $this->expectsEvents(ChangedAvatar::class);

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

        $this->from('profile')
            ->post('/profile/avatar', $data)
            ->assertRedirect('profile');

        $this->assertSessionHasSuccess('Avatar changed successfully.');

        $user = auth()->user()->fresh();

        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists("upload/users/{$user->avatar}");

        list($width, $height) = getimagesizefromstring(
            Storage::disk('public')->get("upload/users/{$user->avatar}")
        );

        $this->assertEquals(160, $width);
        $this->assertEquals(160, $height);
    }

    /** @test */
    public function update_avatar_with_invalid_image_file()
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

        $this->from('profile')
            ->post('/profile/avatar', $data)
            ->assertRedirect('profile')
            ->assertSessionHasErrors('avatar');

        $user = auth()->user()->fresh();

        Storage::disk('public')->assertMissing("upload/users/{$user->avatar}");
        $this->assertNull($user->avatar);
    }

    /** @test */
    public function update_avatar_external()
    {
        $this->expectsEvents(ChangedAvatar::class);

        $data = ['url' => '//www.gravatar.com/avatar'];
        $this->post(route('profile.update.avatar-external', auth()->id()), $data)
            ->assertRedirect();

        $this->assertSessionHasSuccess('Avatar changed successfully.');

        $this->assertEquals($data['url'], auth()->user()->fresh()->avatar);
    }

    /** @test */
    public function update_user_login_details()
    {
        $data = [
            'email' => 'john@doe.com',
            'username' => 'milos',
            'password' => 'milos123123',
            'password_confirmation' => 'milos123123'
        ];

        $this->from('/profile')
            ->put('profile/login-details', $data)
            ->assertRedirect('/profile');

        $this->assertSessionHasSuccess('Login details updated successfully.');

        $user = auth()->user()->fresh();

        $this->assertEquals($data['email'], $user->email);
        $this->assertEquals($data['username'], $user->username);
        $this->assertTrue(Hash::check($data['password'], $user->password));
    }

    /** @test */
    public function password_is_not_changed_if_omited_on_update()
    {
        auth()->user()->update([
            'email' => 'john@example.com',
            'password' => '123123'
        ]);

        $data = [
            'email' => 'test@test.com',
            'password' => '',
            'password_confirmation' => ''
        ];

        $this->from('/profile')
            ->put('profile/login-details', $data)
            ->assertRedirect('/profile');

        $user = auth()->user()->fresh();

        $this->assertEquals($data['email'], $user->email);
        $this->assertTrue(Hash::check('123123', $user->password));
    }

    /** @test */
    public function user_session_invalidation()
    {
        $this->withoutExceptionHandling();

        config(['session.driver' => 'database']);

        $user = UserFactory::withCredentials('foo', 'bar')->create();

        $this->be($user);

        $agent = $this->app['agent'];
        $device = $agent->device() ?: 'Unknown';
        $platform = $agent->platform() ?: 'Unknown';

        // Log-in manually to actually create session record in DB
        $this->post('/login', ['username' => 'foo', 'password' => 'bar']);

        $this->get('/profile/sessions')
            ->assertSee('127.0.0.1')
            ->assertSee($device)
            ->assertSee($platform)
            ->assertSee($agent->browser());

        $sessionId = \DB::table('sessions')->where('user_id', $user->id)->first()->id;
        $this->delete("profile/sessions/{$sessionId}/invalidate");

        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
    }

    /**
     * @return array
     */
    private function getStubDetailsData()
    {
        return [
            'first_name' => 'foo',
            'last_name' => 'bar',
            'birthday' => Carbon::now()->subYears(25)->format('Y-m-d'),
            'phone' => '12345667',
            'address' => 'the address',
            'country_id' => 688 //Serbia,
        ];
    }
}
