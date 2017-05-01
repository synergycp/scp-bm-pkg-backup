<?php

namespace Packages\Backup\App\Archive\Dest;

use Packages\Backup\App\Archive;
use Illuminate\Contracts\Queue;

/**
 * Copy a Backup from its temp file to its destination.
 */
class CopyBackupToDest
implements Queue\ShouldQueue
{
    /**
     * @var DestService
     */
    protected $service;

    /**
     * @param DestService $service
     */
    public function __construct(DestService $service)
    {
        $this->service = $service;
    }

    /**
     * @param Archive\Events\ArchiveEvent $event
     *
     * @throws CopyToDestFailed
     */
    public function handle(Archive\Events\ArchiveEvent $event)
    {
        $this->service->copy($event->target);
    }
}
