<?php

namespace Packages\Backup\App\Archive;

use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

class FireArchiveCompleted
{
    /**
     * @var EventDispatcher
     */
    protected $event;

    /**
     * @param EventDispatcher $event
     */
    public function __construct(EventDispatcher $event)
    {
        $this->event = $event;
    }

    /**
     * @param Events\ArchiveEvent $event
     */
    public function handle(Events\ArchiveEvent $event)
    {
        $this->event->fire(
            new Events\ArchiveCompleted($event->target)
        );
    }
}
