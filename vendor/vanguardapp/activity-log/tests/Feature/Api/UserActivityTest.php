<?php

namespace Vanguard\UserActivity\Tests\Feature\Http\Controllers\Api;

use Facades\Tests\Setup\UserFactory;
use Tests\Feature\ApiTestCase;
use Vanguard\User;
use Vanguard\UserActivity\Activity;
use Vanguard\UserActivity\Transformers\ActivityTransformer;

class UserActivityTest extends ApiTestCase
{
    /** @test */
    public function auth()
    {
        $user = factory(User::class)->create();

        $this->getJson("/api/users/{$user->id}/activity")
            ->assertStatus(401);
    }

    /** @test */
    public function cannot_view_user_activity_without_permission()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'api')
            ->getJson("/api/users/{$user->id}/activity")
            ->assertStatus(403);
    }

    /** @test */
    public function paginate_activities_for_user()
    {
        $user = UserFactory::user()->withPermissions('users.activity')->create();

        $this->be($user, 'api');

        $activities = factory(Activity::class)->times(25)->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/users/{$user->id}/activity");

        $transformed = $this->transformCollection(
            $activities->take(20),
            new ActivityTransformer
        );

        $this->assertEquals($response->original['data'], $transformed);
        $this->assertEquals($response->original['meta'], [
            'current_page' => 1,
            'from' => 1,
            'to' => 20,
            'last_page' => 2,
            'prev_page_url' => null,
            'next_page_url' => url("api/users/{$user->id}/activity?page=2"),
            'total' => 25,
            'per_page' => 20
        ]);
    }

    /** @test */
    public function paginate_activities_for_user_with_search_param()
    {
        $user = UserFactory::user()->withPermissions('users.activity')->create();

        $this->be($user, 'api');

        $set1 = factory(Activity::class)->times(10)->create([
            'user_id' => $user->id,
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
        ]);

        $set2 = factory(Activity::class)->times(5)->create([
            'user_id' => $user->id,
            'description' => 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris...'
        ]);

        $transformed = $this->transformCollection(
            $set2,
            new ActivityTransformer
        );

        $response = $this->getJson("/api/users/{$user->id}/activity?search=minim&per_page=10");

        $this->assertEquals($response->original['data'], $transformed);
        $this->assertEquals($response->original['meta'], [
            'current_page' => 1,
            'from' => 1,
            'to' => 5,
            'last_page' => 1,
            'prev_page_url' => null,
            'next_page_url' => null,
            'total' => 5,
            'per_page' => 10
        ]);
    }

    /** @test */
    public function paginate_activities_for_user_with_more_activities_per_page_than_allowed()
    {
        $user = UserFactory::user()->withPermissions('users.activity')->create();

        $this->actingAs($user, 'api')
            ->getJson("/api/users/{$user->id}/activity?per_page=140")
            ->assertStatus(422);
    }
}
