<?php

namespace Vanguard\Announcements\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Vanguard\Announcements\Announcement;

class Updated
{
    use Dispatchable;

    public $announcement;

    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }
}
