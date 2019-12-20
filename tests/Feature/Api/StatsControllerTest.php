<?php

namespace Tests\Feature\Http\Controllers\Api;

use Carbon\Carbon;
use Tests\Feature\ApiTestCase;
use Vanguard\Repositories\User\UserRepository;
use Vanguard\Role;
use Vanguard\Support\Enum\UserStatus;
use Vanguard\Transformers\UserTransformer;
use Vanguard\User;

class StatsControllerTest extends ApiTestCase
{
    /** @test */
    public function unauthenticated()
    {
        $this->getJson('/api/stats')->assertStatus(401);
    }

    /** @test */
    public function get_stats_as_admin()
    {
        \DB::table('users')->delete();

        $adminRole = Role::whereName('Admin')->first();

        $user = factory(User::class)->create(['role_id' => $adminRole->id]);

        $this->be($user, 'api');

        Carbon::setTestNow(Carbon::now()->startOfYear());

        factory(User::class)->times(4)->create(['status' => UserStatus::ACTIVE]);

        Carbon::setTestNow(null);

        factory(User::class)->times(2)->create(['status' => UserStatus::BANNED]);

        factory(User::class)->times(7)->create(['status' => UserStatus::UNCONFIRMED]);

        $users = app(UserRepository::class);

        $response = $this->getJson("/api/stats");

        $usersPerMonth = $users->countOfNewUsersPerMonth(
            now()->subYear()->startOfMonth(),
            now()->endOfMonth()
        );

        $latestRegistrations = $users->latest(7);

        $response->assertOk()
            ->assertJson([
                'users_per_month' => $usersPerMonth,
                'users_per_status' => [
                    'total' => 14,
                    'new' => $users->newUsersCount(),
                    'banned' => 2,
                    'unconfirmed' => 7
                ],
                'latest_registrations' => $this->transformCollection($latestRegistrations, new UserTransformer)
            ]);
    }

    /** @test */
    public function non_admin_users_cannot_get_user_stats()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'api')->getJson("/api/stats")->assertForbidden();
    }
}
