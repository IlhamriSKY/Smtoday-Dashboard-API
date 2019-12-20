<?php

namespace Vanguard\UserActivity\Tests\Unit\Repositories\Activity;

use Illuminate\Foundation\Testing\Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Vanguard\User;
use Carbon\Carbon;
use Vanguard\UserActivity\Activity;
use Vanguard\UserActivity\Repositories\Activity\EloquentActivity;

class EloquentActivityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var EloquentActivity
     */
    protected $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(EloquentActivity::class);
    }

    /** @test */
    public function log()
    {
        $user = factory(User::class)->create();

        Carbon::setTestNow(Carbon::now());

        $data = [
            'user_id' => $user->id,
            'ip_address' => '123.456.789.012',
            'user_agent' => 'foo',
            'description' => 'descriptionnnn'
        ];

        $this->repo->log($data);

        $this->assertDatabaseHas('user_activity', $data);
    }

    /** @test */
    public function paginate_activities_for_user()
    {
        $user = factory(User::class)->create();

        $activities = factory(Activity::class)->times(10)->create(['user_id' => $user->id]);

        $result = $this->repo->paginateActivitiesForUser($user->id, 6)->toArray();

        $this->assertEquals(6, count($result['data']));
        $this->assertEquals(10, $result['total']);
        $this->assertEquals($activities[0]->toArray(), $result['data'][0]);
        $this->assertEquals($activities[5]->toArray(), $result['data'][5]);
    }

    /** @test */
    public function latest_activities_for_user()
    {
        $user = factory(User::class)->create();

        Carbon::setTestNow(Carbon::now()->subDay());
        $activities1 = factory(Activity::class)->times(5)->create(['user_id' => $user->id]);

        Carbon::setTestNow(null);
        $activities2 = factory(Activity::class)->times(5)->create(['user_id' => $user->id]);

        $result = $this->repo->getLatestActivitiesForUser($user->id, 6)->toArray();

        $this->assertEquals(6, count($result));
        $this->assertEquals($activities2[0]->toArray(), $result[0]);
        $this->assertEquals($activities1[0]->toArray(), $result[5]);
    }

    /** @test */
    public function paginate_activities()
    {
        $activities = factory(Activity::class)->times(10)->create();

        $result = $this->repo->paginateActivities(6)->toArray();

        $this->assertEquals(6, count($result['data']));
        $this->assertEquals(10, $result['total']);

        Assert::assertArraySubset($activities[0]->toArray(), $result['data'][0]);
        Assert::assertArraySubset($activities[5]->toArray(), $result['data'][5]);
    }

    /** @test */
    public function userActivityForPeriod()
    {
        $user = factory(User::class)->create();
        $now = Carbon::now();

        Carbon::setTestNow($now->copy()->subDays(15));
        factory(Activity::class)->times(5)->create(['user_id' => $user->id]);

        Carbon::setTestNow($now->copy()->subDays(11));
        factory(Activity::class)->times(2)->create(['user_id' => $user->id]);

        Carbon::setTestNow($now->copy()->subDays(5));
        factory(Activity::class)->times(3)->create(['user_id' => $user->id]);

        Carbon::setTestNow($now->copy()->subDays(2));
        factory(Activity::class)->times(2)->create(['user_id' => $user->id]);

        Carbon::setTestNow(null);

        $result = $this->repo->userActivityForPeriod(
            $user->id,
            Carbon::now()->subWeeks(2),
            Carbon::now()
        );

        $this->assertEquals($result->get(Carbon::now()->subDays(14)->toDateString()), 0);
        $this->assertEquals($result->get(Carbon::now()->subDays(13)->toDateString()), 0);
        $this->assertEquals($result->get(Carbon::now()->subDays(12)->toDateString()), 0);
        $this->assertEquals($result->get(Carbon::now()->subDays(11)->toDateString()), 2);
        $this->assertEquals($result->get(Carbon::now()->subDays(10)->toDateString()), 0);
        $this->assertEquals($result->get(Carbon::now()->subDays(9)->toDateString()), 0);
        $this->assertEquals($result->get(Carbon::now()->subDays(8)->toDateString()), 0);
        $this->assertEquals($result->get(Carbon::now()->subDays(7)->toDateString()), 0);
        $this->assertEquals($result->get(Carbon::now()->subDays(6)->toDateString()), 0);
        $this->assertEquals($result->get(Carbon::now()->subDays(5)->toDateString()), 3);
        $this->assertEquals($result->get(Carbon::now()->subDays(4)->toDateString()), 0);
        $this->assertEquals($result->get(Carbon::now()->subDays(3)->toDateString()), 0);
        $this->assertEquals($result->get(Carbon::now()->subDays(2)->toDateString()), 2);
        $this->assertEquals($result->get(Carbon::now()->subDays(1)->toDateString()), 0);
        $this->assertEquals($result->get(Carbon::now()->toDateString()), 0);
    }
}
