<?php

namespace Packages\Backup\App\Backup;

/**
 * Mark the status of a Backup based on a BackupStatusChange.
 */
class MarkArchiveStatus
{
    /**
     * @param Events\BackupStatusChangeEvent $event
     */
    public function handle(Events\BackupStatusChangeEvent $event)
    {
        $backup = $event->target;
        $backup->status = $event->status();
        $backup->save();
    }
}
