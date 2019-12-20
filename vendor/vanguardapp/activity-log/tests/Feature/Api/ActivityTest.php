<?php

namespace Vanguard\UserActivity\Tests\Feature\Api;

use Facades\Tests\Setup\UserFactory;
use Tests\Feature\ApiTestCase;
use Vanguard\UserActivity\Activity;
use Vanguard\UserActivity\Transformers\ActivityTransformer;
use Vanguard\User;

class ActivityTest extends ApiTestCase
{
    /** @test */
    public function unauthenticated()
    {
        $this->getJson('/api/activity')->assertStatus(401);
    }

    /** @test */
    public function get_activities_without_permission()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/activity')
            ->assertStatus(403);
    }

    /** @test */
    public function paginate_activities()
    {
        $user = $this->getUser();
        $user2 = factory(User::class)->create();

        $activities = factory(Activity::class)->times(25)->create(['user_id' => $user->id]);

        factory(Activity::class)->times(10)->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user, 'api')->getJson("/api/activity");

        $transformed = $this->transformCollection($activities->take(20), new ActivityTransformer);

        $this->assertEquals($response->original['data'], $transformed);
        $this->assertEquals($response->original['meta'], [
            'current_page' => 1,
            'from' => 1,
            'to' => 20,
            'last_page' => 2,
            'prev_page_url' => null,
            'next_page_url' => url("api/activity?page=2"),
            'total' => 35,
            'per_page' => 20
        ]);
    }

    /** @test */
    public function paginate_activities_with_search_param()
    {
        $user = $this->getUser();

        $set1 = factory(Activity::class)->times(10)->create([
            'user_id' => $user->id,
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
        ]);

        $set2 = factory(Activity::class)->times(5)->create([
            'user_id' => $user->id,
            'description' => 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris...'
        ]);

        $transformed = $this->transformCollection($set2, new ActivityTransformer);

        $response = $this->actingAs($user, 'api')
            ->getJson("/api/activity?search=minim&per_page=10")
            ->assertOk();

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
    public function paginate_activities_with_more_records_per_page_than_allowed()
    {
        $this->actingAs($this->getUser(), 'api')
            ->getJson("/api/activity?per_page=140")
            ->assertStatus(422);
    }

    /**
     * @return mixed
     */
    private function getUser()
    {
        return UserFactory::user()->withPermissions('users.activity')->create();
    }
}
