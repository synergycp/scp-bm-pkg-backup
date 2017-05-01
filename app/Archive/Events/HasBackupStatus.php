<?php

namespace Packages\Backup\App\Archive\Events;

use Packages\Backup\App\Archive;

/**
 * Implement the ArchiveStatusChangeEvent interface for a ArchiveEvent.
 */
trait HasBackupStatus
{
    /**
     * @return Archive\Archive
     */
    public function target()
    {
        return $this->target;
    }

    /**
     * @return int
     */
    public function status()
    {
        return $this->status;
    }
}
