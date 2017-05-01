<?php

namespace Packages\Backup\App\Backup\Dest;

use Packages\Backup\App\Backup;
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
     * @param Backup\Events\ArchiveEvent $event
     *
     * @throws CopyToDestFailed
     */
    public function handle(Backup\Events\ArchiveEvent $event)
    {
        $this->service->copy($event->target);
    }
}
