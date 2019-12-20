<?php

namespace Vanguard\UserActivity\Tests\Unit\Listeners;

use Mockery as m;
use Vanguard\Events\Permission\Created;
use Vanguard\Events\Permission\Deleted;
use Vanguard\Events\Permission\Updated;
use \Vanguard\UserActivity\Tests\Unit\Listeners\ListenerTestCase;

class PermissionEventsSubscriberTest extends ListenerTestCase
{
    protected $perm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->perm = factory(\Vanguard\Permission::class)->create();
    }

    /** @test */
    public function onCreate()
    {
        event(new Created($this->perm));
        $this->assertMessageLogged("Created new permission called {$this->perm->display_name}.");
    }

    /** @test */
    public function onUpdate()
    {
        event(new Updated($this->perm));
        $this->assertMessageLogged("Updated the permission named {$this->perm->display_name}.");
    }

    /** @test */
    public function onDelete()
    {
        event(new Deleted($this->perm));
        $this->assertMessageLogged("Deleted permission named {$this->perm->display_name}.");
    }
}
