<?php

namespace Packages\Backup\App\Backup\Listeners;

use Packages\Backup\App\Backup;
use Illuminate\Contracts\Queue;

/**
 * Start a Backup.
 */
class BackupStart
implements Queue\ShouldQueue
{
    /**
     * @var Backup\BackupToFileService
     */
    protected $service;

    /**
     * @param Backup\BackupToFileService $service
     */
    public function __construct(Backup\BackupToFileService $service)
    {
        $this->service = $service;
    }

    /**
     * @param Backup\Events\BackupEvent $event
     */
    public function handle(Backup\Events\BackupEvent $event)
    {
        $this->service->backup($event->target);
    }
}
