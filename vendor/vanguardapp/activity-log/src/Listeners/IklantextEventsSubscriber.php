<?php

namespace Vanguard\UserActivity\Listeners;

use Vanguard\Events\Smtoday\Iklantext\Created;
use Vanguard\Events\Smtoday\Iklantext\IklantextUpdated;
use Vanguard\Events\Smtoday\Iklantext\Updated;
use Vanguard\Events\Smtoday\Iklantext\Deleted;
use Vanguard\UserActivity\Logger;

class IklantextEventsSubscriber
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
            'user-activity::log.new_iklantext',
            ['name' => $event->getIklantext()->nama]
        );

        $this->logger->log($message);
    }

    public function onUpdate(Updated $event)
    {
        $message = trans(
            'user-activity::log.updated_iklantext',
            ['name' => $event->getIklantext()->nama]
        );

        $this->logger->log($message);
    }

    public function onDelete(Deleted $event)
    {
        $message = trans(
            'user-activity::log.deleted_iklantext',
            ['name' => $event->getIklantext()->nama]
        );

        $this->logger->log($message);
    }

    // public function onDelete(Deleted $event)
    // {
    //     $iklantext = $event->getIklantext();

    //     $name = $iklantext->nama;
    //     $message = trans('user-activity::log.deleted_iklantext', ['name' => $name]);

    //     $this->logger->log($message);
    // }

    public function onIklantextUpdate(IklantextUpdated $event)
    {
        $this->logger->log(trans('user-activity::log.updated_iklan_text'));
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
        $events->listen(IklantextUpdated::class, "{$class}@onIklantextUpdate");
    }
}
