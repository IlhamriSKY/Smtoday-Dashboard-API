<?php

namespace Vanguard\UserActivity\Listeners;

use Vanguard\Events\Smtoday\Beritatext\Created;
use Vanguard\Events\Smtoday\Beritatext\BeritatextUpdated;
use Vanguard\Events\Smtoday\Beritatext\Updated;
use Vanguard\Events\Smtoday\Beritatext\Deleted;
use Vanguard\UserActivity\Logger;

class BeritatextEventsSubscriber
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
            'user-activity::log.new_beritatext',
            ['name' => $event->getBeritatext()->judul]
        );

        $this->logger->log($message);
    }

    public function onUpdate(Updated $event)
    {
        $message = trans(
            'user-activity::log.updated_beritatext',
            ['name' => $event->getBeritatext()->judul]
        );

        $this->logger->log($message);
    }

    public function onDelete(Deleted $event)
    {
        $message = trans(
            'user-activity::log.deleted_beritatext',
            ['name' => $event->getBeritatext()->judul]
        );

        $this->logger->log($message);
    }

    // public function onDelete(Deleted $event)
    // {
    //     $beritatext = $event->getberitatext();

    //     $name = $beritatext->nama;
    //     $message = trans('user-activity::log.deleted_beritatext', ['name' => $name]);

    //     $this->logger->log($message);
    // }

    public function onBeritatextUpdate(BeritatextUpdated $event)
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
        $events->listen(BeritatextUpdated::class, "{$class}@onBeritatextUpdate");
    }
}
