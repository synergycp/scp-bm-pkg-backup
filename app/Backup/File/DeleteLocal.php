<?php

namespace Packages\Backup\App\Backup\File;

use Packages\Backup\App\Backup;
use Illuminate\Contracts\Queue;

/**
 * Delete the local copy of a Backup after it has been copied to its destination.
 */
class DeleteLocal
implements Queue\ShouldQueue
{
    /**
     * @var FileService
     */
    protected $file;

    /**
     * @param FileService $file
     */
    public function __construct(FileService $file)
    {
        $this->file = $file;
    }

    /**
     * @param Backup\Events\BackupEvent $event
     */
    public function handle(Backup\Events\BackupEvent $event)
    {
        $this->file->delete($event->target);
    }
}
