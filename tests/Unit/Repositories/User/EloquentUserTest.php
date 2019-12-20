<?php

namespace Tests\Unit\Repositories\User;

use Carbon\Carbon;
use DB;
use Facades\Tests\Setup\UserFactory;
use Illuminate\Foundation\Testing\Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Vanguard\Repositories\User\EloquentUser;
use Vanguard\Role;
use Vanguard\Support\Enum\UserStatus;
use Vanguard\User;

class EloquentUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var EloquentUser
     */
    protected $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(EloquentUser::class);
    }

    /** @test */
    public function find()
    {
        $user = factory(User::class)->create();

        $this->assertTrue($user->is($this->repo->findByEmail($user->email)));

        $this->assertNull($this->repo->find(123));
    }

    /** @test */
    public function find_by_email()
    {
        $user = factory(User::class)->create();

        $this->assertTrue($user->is($this->repo->findByEmail($user->email)));

        $this->assertNull($this->repo->findByEmail('foo@bar.com'));
    }

    /** @test */
    public function find_by_social_id()
    {
        $user = factory(User::class)->create();

        DB::table('social_logins')->insert([
            'user_id' => $user->id,
            'provider' => 'foo',
            'provider_id' => '123',
            'avatar' => '',
            'created_at' => Carbon::now()
        ]);

        Assert::assertArraySubset(
            $user->toArray(),
            $this->repo->findBySocialId('foo', '123')->toArray()
        );

        $this->assertNull($this->repo->findBySocialId('bar', '111'));
    }

    /** @test */
    public function find_by_session_id()
    {
        $user = factory(User::class)->create();

        $sessionId = Str::random(40);

        DB::table('sessions')->insert([
            'id' => $sessionId,
            'user_id' => $user->id,
            'ip_address' => "127.0.0.1",
            'user_agent' => "foo",
            'payload' => Str::random(),
            'last_activity' => Carbon::now()
        ]);

        Assert::assertArraySubset(
            $user->toArray(),
            $this->repo->findBySessionId($sessionId)->toArray()
        );
    }

    /** @test */
    public function create()
    {
        $data = factory(User::class)->make()->toArray();

        $this->repo->create($data + ['password' => 'foo']);

        $this->assertDatabaseHas('users', $data);
    }

    /** @test */
    public function associate_facebook_account_to_a_user()
    {
        $user = factory(User::class)->create();

        Carbon::setTestNow(Carbon::now());

        $socialUser = new \Laravel\Socialite\One\User();
        $socialUser->map([
            'id' => '123',
            'avatar' => 'foo',
            'avatar_original' => 'foo?width=1920'
        ]);

        $this->repo->associateSocialAccountForUser($user->id, 'facebook', $socialUser);

        $this->assertDatabaseHas('social_logins', [
            'user_id' => $user->id,
            'provider' => 'facebook',
            'provider_id' => '123',
            'avatar' => 'foo?width=150',
            'created_at' => Carbon::now()
        ]);

        Carbon::setTestNow(null);
    }

    /** @test */
    public function associate_twitter_account_to_a_user()
    {
        $user = factory(User::class)->create();

        Carbon::setTestNow(Carbon::now());

        $socialUser = new \Laravel\Socialite\One\User();
        $socialUser->map([
            'id' => '123',
            'avatar' => 'foo_normal.jpg'
        ]);

        $this->repo->associateSocialAccountForUser($user->id, 'twitter', $socialUser);

        $this->assertDatabaseHas('social_logins', [
            'user_id' => $user->id,
            'provider' => 'twitter',
            'provider_id' => '123',
            'avatar' => 'foo_200x200.jpg',
            'created_at' => Carbon::now()
        ]);

        Carbon::setTestNow(null);
    }

    /** @test */
    public function associate_google_account_to_a_user()
    {
        $user = factory(User::class)->create();

        Carbon::setTestNow(Carbon::now());

        $socialUser = new \Laravel\Socialite\One\User();
        $socialUser->map([
            'id' => '123',
            'avatar' => 'avatar.jpg',
            'avatar_original' => 'avatar_original.jpg'
        ]);

        $this->repo->associateSocialAccountForUser($user->id, 'google', $socialUser);

        $this->assertDatabaseHas('social_logins', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => '123',
            'avatar' => 'avatar_original.jpg?sz=150',
            'created_at' => Carbon::now()
        ]);

        Carbon::setTestNow(null);
    }

    /** @test */
    public function paginate()
    {
        $users = factory(User::class)->times(5)->create();
        $users = $users->sortByDesc('id')->values();

        $result = $this->repo->paginate(2)->toArray();

        $this->assertEquals(2, count($result['data']));
        $this->assertEquals(5, $result['total']);
        Assert::assertArraySubset($users[0]->toArray(), $result['data'][0]);
        Assert::assertArraySubset($users[1]->toArray(), $result['data'][1]);
    }

    /** @test */
    public function paginate_with_status()
    {
        factory(User::class)->times(3)->create();
        factory(User::class)->create(['status' => UserStatus::BANNED]);

        $active = $this->repo->paginate(2, null, UserStatus::ACTIVE)->toArray();
        $banned = $this->repo->paginate(2, null, UserStatus::BANNED)->toArray();

        $this->assertEquals(2, count($active['data']));
        $this->assertEquals(3, $active['total']);

        $this->assertEquals(1, count($banned['data']));
        $this->assertEquals(1, $banned['total']);
    }

    /** @test */
    public function paginate_with_search()
    {
        factory(User::class)->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'jdoe',
            'email' => 'joe@test.com'
        ]);

        factory(User::class)->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'username' => 'janedoe',
            'email' => 'jane@doe.com'
        ]);

        factory(User::class)->create([
            'first_name' => 'Milos',
            'last_name' => 'Stojanovic',
            'email' => 'test@test.com'
        ]);

        $this->assertEquals(2, $this->repo->paginate(25, 'doe')->total());
        $this->assertEquals(1, $this->repo->paginate(25, 'Milos')->total());
        $this->assertEquals(2, $this->repo->paginate(25, 'test')->total());
        $this->assertEquals(2, $this->repo->paginate(25, 'an')->total());
    }

    /** @test */
    public function update_user()
    {
        $user = factory(User::class)->create();

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'foo',
            'email' => 'test@test.com'
        ];

        $this->repo->update($user->id, $data);

        $this->assertDatabaseHas('users', $data + ['id' => $user->id]);
    }

    /** @test */
    public function update_when_provided_country_is_zero()
    {
        $user = factory(User::class)->create();

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'foo',
            'email' => 'test@test.com',
            'country_id' => 0
        ];

        $this->repo->update($user->id, $data);

        $this->assertDatabaseHas('users', array_merge($data, ['id' => $user->id, 'country_id' => null]));
    }

    /** @test */
    public function delete_user()
    {
        $user = factory(User::class)->create();

        $this->repo->delete($user->id);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /** @test */
    public function count_users()
    {
        factory(User::class)->times(7)->create();

        $this->assertEquals(7, $this->repo->count());
    }

    /** @test */
    public function new_users_count()
    {
        Carbon::setTestNow(Carbon::now()->startOfMonth()->subMonth());
        factory(User::class)->times(3)->create();

        Carbon::setTestNow(null);
        factory(User::class)->times(5)->create();

        $this->assertEquals(5, $this->repo->newUsersCount());
    }

    /** @test */
    public function count_by_status()
    {
        factory(User::class)->times(3)->create();
        factory(User::class)->create(['status' => UserStatus::BANNED]);
        factory(User::class)->times(2)->create(['status' => UserStatus::UNCONFIRMED]);

        $this->assertEquals(3, $this->repo->countByStatus(UserStatus::ACTIVE));
        $this->assertEquals(1, $this->repo->countByStatus(UserStatus::BANNED));
        $this->assertEquals(2, $this->repo->countByStatus(UserStatus::UNCONFIRMED));
    }

    /** @test */
    public function latest()
    {
        Carbon::setTestNow(now()->subDay());
        $user4 = factory(User::class)->create();

        Carbon::setTestNow(now()->subMinutes(3));
        $user3 = factory(User::class)->create();

        Carbon::setTestNow(now()->subMinutes(2));
        $user2 = factory(User::class)->create();

        Carbon::setTestNow(now()->subMinutes(1));
        $user1 = factory(User::class)->create();

        $latestTwo = $this->repo->latest(2);
        $latestFour = $this->repo->latest(4);

        $this->assertEquals(2, count($latestTwo));
        $this->assertEquals(4, count($latestFour));

        Assert::assertArraySubset($user4->toArray(), $latestTwo[0]->toArray());
        Assert::assertArraySubset($user3->toArray(), $latestTwo[1]->toArray());
        Assert::assertArraySubset($user1->toArray(), $latestFour[3]->toArray());
    }

    /** @test */
    public function count_of_new_users_per_month()
    {
        $from = now()->startOfYear();

        Carbon::setTestNow($from);
        factory(User::class)->times(2)->create();

        Carbon::setTestNow($from->copy()->addMonths(2));
        factory(User::class)->times(4)->create();

        Carbon::setTestNow($from->copy()->addMonths(6));
        factory(User::class)->times(2)->create();

        Carbon::setTestNow($from->copy()->addMonths(7));
        factory(User::class)->times(1)->create();

        Carbon::setTestNow($from->copy()->addMonths(10));
        factory(User::class)->times(4)->create();

        Carbon::setTestNow(null);

        $currentYear = now()->year;

        $expected = [
            "January {$currentYear}" => 2,
            "February {$currentYear}" => 0,
            "March {$currentYear}" => 4,
            "April {$currentYear}" => 0,
            "May {$currentYear}" => 0,
            "June {$currentYear}" => 0,
            "July {$currentYear}" => 2,
            "August {$currentYear}" => 1,
            "September {$currentYear}" => 0,
            "October {$currentYear}" => 0,
            "November {$currentYear}" => 4,
            "December {$currentYear}" => 0
        ];

        $usersPerMonth = $this->repo->countOfNewUsersPerMonth(
            now()->startOfYear(),
            now()->endOfYear()
        );

        $this->assertEquals($expected, $usersPerMonth);
    }

    /** @test */
    public function get_users_with_specific_role()
    {
        $this->artisan('db:seed --class=RolesSeeder');

        $admin = UserFactory::admin()->create();
        $user = UserFactory::user()->create();

        $result = $this->repo->getUsersWithRole('Admin');

        $this->assertEquals(1, $result->count());
        $this->assertTrue($admin->is($result[0]));

        $result = $this->repo->getUsersWithRole('User');
        $this->assertEquals(1, $result->count());
        $this->assertTrue($user->is($result[0]));
    }

    /** @test */
    public function set_role()
    {
        $this->artisan('db:seed --class=RolesSeeder');

        $user = UserFactory::user()->create();
        $role = Role::where('name', 'Admin')->first();

        $this->repo->setRole($user->id, $role->id);

        $this->assertDatabaseHas('users', [
           'role_id' => $role->id,
           'id' => $user->id
        ]);
    }

    /** @test */
    public function switch_roles_for_a_user()
    {
        $this->artisan('db:seed --class=RolesSeeder');

        $adminRole = Role::where('name', 'Admin')->first();

        $userA = UserFactory::user()->create();
        $userB = UserFactory::user()->create();

        $this->repo->switchRolesForUsers($userA->role_id, $adminRole->id);

        $this->assertDatabaseHas('users', [
            'role_id' => $adminRole->id,
            'id' => $userA->id
        ]);

        $this->assertDatabaseHas('users', [
            'role_id' => $adminRole->id,
            'id' => $userB->id
        ]);
    }
}
