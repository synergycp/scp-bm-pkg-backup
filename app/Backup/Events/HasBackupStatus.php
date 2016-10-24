<?php

namespace Packages\Backup\App\Backup\Events;

use Packages\Backup\App\Backup;

/**
 * Implement the BackupStatusChangeEvent interface for a BackupEvent.
 */
trait HasBackupStatus
{
    /**
     * @return Backup\Backup
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
