<?php

namespace Packages\Backup\App\Backup;

use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

class FireBackupCompleted
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
     * @param Events\BackupEvent $event
     */
    public function handle(Events\BackupEvent $event)
    {
        $this->event->fire(
            new Events\BackupCompleted($event->target)
        );
    }
}
