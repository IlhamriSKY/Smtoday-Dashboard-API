<?php

namespace Vanguard\UserActivity\Tests\Feature\Web;

use Carbon\Carbon;
use Facades\Tests\Setup\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Vanguard\UserActivity\Logger;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    public $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = app(Logger::class);
        $this->artisan('db:seed');
    }

    /** @test */
    public function display_all_activities()
    {
        $this->withoutMiddleware();

        $user1 = UserFactory::create();
        $user2 = UserFactory::create();

        Carbon::setTestNow(Carbon::now());

        $this->be($user1);
        $this->logger->log('foo');

        $this->be($user2);
        $this->logger->log('bar');

        $this->get('activity')
            ->assertSee('foo')
            ->assertSee('bar');
    }

    /** @test */
    public function display_activities_for_a_specific_user()
    {
        $user = UserFactory::admin()->create();
        $this->be($user);

        $this->logger->log('foo');

        $this->get("activity/user/{$user->id}/log")
            ->assertSee('foo');
    }

    /** @test */
    public function search_activities()
    {
        $this->withoutMiddleware();

        $user = UserFactory::create();
        $this->be($user);

        $this->logger->log('foo');
        $this->logger->log('barrr');

        $this->get('activity?search=foo')
            ->assertSee('foo')
            ->assertDontSee('barrr');
    }
}
