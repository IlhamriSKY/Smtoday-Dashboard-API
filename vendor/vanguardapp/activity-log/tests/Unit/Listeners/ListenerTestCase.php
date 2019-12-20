<?php

namespace Vanguard\UserActivity\Tests\Unit\Listeners;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Vanguard\User;

class ListenerTestCase extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->be($this->user);
    }

    protected function assertMessageLogged($msg, $user = null)
    {
        $this->assertDatabaseHas('user_activity', [
            'user_id' => $user ? $user->id : $this->user->id,
            'ip_address' => \Request::ip(),
            'user_agent' => \Request::header('User-agent'),
            'description' => $msg
        ]);
    }
}
