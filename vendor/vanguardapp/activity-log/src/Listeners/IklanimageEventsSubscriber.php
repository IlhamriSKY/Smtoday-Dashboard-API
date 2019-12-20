<?php

namespace Vanguard\UserActivity\Listeners;

use Vanguard\Events\Smtoday\Iklanimage\Created;
use Vanguard\Events\Smtoday\Iklanimage\IklanimageUpdated;
use Vanguard\Events\Smtoday\Iklanimage\Updated;
use Vanguard\Events\Smtoday\Iklanimage\Deleted;
use Vanguard\UserActivity\Logger;

class IklanimageEventsSubscriber
{
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function onCreate(Created $event)
    {
        $message = trans(
            'user-activity::log.new_iklanimage',
            ['name' => $event->getIklanimage()->nama]
        );

        $this->logger->log($message);
    }

    public function onUpdate(Updated $event)
    {
        $message = trans(
            'user-activity::log.updated_iklanimage',
            ['name' => $event->getIklanimage()->nama]
        );

        $this->logger->log($message);
    }

    public function onDelete(Deleted $event)
    {
        $message = trans(
            'user-activity::log.deleted_iklanimage',
            ['name' => $event->getIklanimage()->nama]
        );

        $this->logger->log($message);
    }

    // public function onDelete(Deleted $event)
    // {
    //     $iklantext = $event->getIklanimage();

    //     $name = $iklantext->nama;
    //     $message = trans('user-activity::log.deleted_iklantext', ['name' => $name]);

    //     $this->logger->log($message);
    // }

    public function onIklanimageUpdate(IklanimageUpdated $event)
    {
        $this->logger->log(trans('user-activity::log.updated_iklan_image'));
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $class = self::class;

        $events->listen(Created::class, "{$class}@onCreate");
        $events->listen(Updated::class, "{$class}@onUpdate");
        $events->listen(Deleted::class, "{$class}@onDelete");
        $events->listen(IklanimageUpdated::class, "{$class}@onIklanimageUpdate");
    }
}
