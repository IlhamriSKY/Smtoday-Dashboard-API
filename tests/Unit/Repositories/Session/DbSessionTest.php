<?php

namespace Tests\Unit\Repositories\Session;

use Carbon\Carbon;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Vanguard\Repositories\Session\DbSession;
use Vanguard\User;

class DbSessionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var DbSession
     */
    protected $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(DbSession::class);
    }

    /** @test */
    public function get_user_session()
    {
        $user = factory(User::class)->create();

        Carbon::setTestNow(Carbon::now());

        $data1 = $this->getSessionStubData($user);
        $data2 = $this->getSessionStubData($user);

        DB::table('sessions')->insert($data1);
        DB::table('sessions')->insert($data2);

        $expected = collect([
            (object) $this->addAddMissingFields($data1),
            (object) $this->addAddMissingFields($data2),
        ]);
        $expected = $expected->sortBy('id')->keyBy('id')->toArray();

        $actual = $this->repo->getUserSessions($user->id)
            ->sortBy('id')
            ->keyBy('id')
            ->toArray();

        $this->assertEquals($expected, $actual);
    }

    private function addAddMissingFields(array $data)
    {
        $agent = app('agent');
        $agent->setUserAgent($data['user_agent']);

        return array_merge($data, [
            'browser' => $agent->browser(),
            'platform' => $agent->platform(),
            'device' => $agent->device(),
            'last_activity' => Carbon::createFromTimestamp($data['last_activity'])
        ]);
    }

    /** @test */
    public function if_get_user_sessions_will_return_active_sessions_only()
    {
        $user = factory(User::class)->create();

        Carbon::setTestNow(Carbon::now());

        $data1 = $this->getSessionStubData($user);
        $data2 = $this->getSessionStubData($user);
        $data2['last_activity'] = Carbon::now()->subMinutes(config('session.lifetime') + 1)->timestamp;

        DB::table('sessions')->insert($data1);
        DB::table('sessions')->insert($data2);

        $expected = collect([
            (object) $this->addAddMissingFields($data1)
        ]);

        $expected = $expected->sortBy('id')->keyBy('id')->toArray();

        $actual = $this->repo->getUserSessions($user->id)
            ->sortBy('id')
            ->keyBy('id')
            ->toArray();

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function invalidate_user_session()
    {
        $user = factory(User::class)->create([
            'remember_token' => Str::random(60)
        ]);

        $data = $this->getSessionStubData($user);
        DB::table('sessions')->insert($data);

        $this->repo->invalidateSession($data['id']);

        $this->assertDatabaseMissing('sessions', $data)
            ->assertDatabaseHas('users', ['remember_token' => null]);
    }

    /** @test */
    public function invalidate_all_sessions_for_user()
    {
        $user = factory(User::class)->create([
            'remember_token' => Str::random(60)
        ]);

        $data = $this->getSessionStubData($user);
        DB::table('sessions')->insert($data);

        $this->repo->invalidateAllSessionsForUser($user->id);

        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id])
            ->assertDatabaseHas('users', ['remember_token' => null]);
    }

    private function getSessionStubData($user)
    {
        $faker = app(\Faker\Generator::class);

        return [
            'id' => Str::random(),
            'user_id' => $user->id,
            'ip_address' => $faker->ipv4,
            'user_agent' => $faker->userAgent,
            'payload' => 'foo',
            'last_activity' => Carbon::now()->timestamp
        ];
    }
}
