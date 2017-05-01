<?php

namespace Packages\Backup\App\Archive\File;

use Packages\Backup\App\Archive;
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
     * @param Archive\Events\ArchiveEvent $event
     */
    public function handle(Backup\Events\ArchiveEvent $event)
    {
        $this->service->save($event->target);
    }
}
