<?php

namespace Packages\Backup\App\Backup\File;

use Packages\Backup\App\Backup;
use Illuminate\Contracts\Queue;

/**
 * Start a Backup.
 */
class BackupToFile
implements Queue\ShouldQueue
{
    /**
     * @var FileService
     */
    protected $service;

    /**
     * @param FileService $service
     */
    public function __construct(FileService $service)
    {
        $this->service = $service;
    }

    /**
     * @param Backup\Events\BackupEvent $event
     */
    public function handle(Backup\Events\BackupEvent $event)
    {
        $this->service->save($event->target);
    }
}
