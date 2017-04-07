<?php

namespace Packages\Backup\App\Backup\Dest;

use Packages\Backup\App\Backup;
use App\Log;

class CopyingBackupToDest
extends Backup\Events\BackupLoggableEvent
implements Backup\Events\BackupStatusChangeEvent
{
    use Backup\Events\HasBackupStatus;

    /**
     * @var int
     */
    protected $status = Backup\ArchiveStatus::COPYING;

    public function log(Log\Log $log)
    {
        $log->setDesc('Copying backup to destination')
            ->setTarget($this->target)
            ->save()
            ;
    }
}
