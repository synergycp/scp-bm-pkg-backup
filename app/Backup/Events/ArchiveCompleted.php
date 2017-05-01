<?php

namespace Packages\Backup\App\Backup\Events;

use App\Log;
use Packages\Backup\App\Backup;

class ArchiveCompleted
extends ArchiveLoggableEvent
implements Backup\Events\ArchiveStatusChangeEvent
{
    use HasBackupStatus;

    /**
     * @var int
     */
    protected $status = Backup\ArchiveStatus::FINISHED;

    public function log(Log\Log $log)
    {
        $log->setDesc('Backup completed')
            ->setTarget($this->target)
            ->save()
            ;
    }
}
