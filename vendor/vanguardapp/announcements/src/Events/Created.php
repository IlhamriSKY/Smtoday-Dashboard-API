<?php

namespace Vanguard\Announcements\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Vanguard\Announcements\Announcement;

class Created
{
    use Dispatchable;

    /**
     * @var Announcement
     */
    public $announcement;

    /**
     * @var bool
     */
    public $shouldSendEmailNotification;

    public function __construct(Announcement $announcement, $sendEmailNotification = false)
    {
        $this->announcement = $announcement;
        $this->shouldSendEmailNotification = $sendEmailNotification;
    }
}
