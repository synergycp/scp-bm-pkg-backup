<?php

namespace Packages\Backup\App\Backup\Events;

use App\Log;

class BackupCreated
extends BackupLoggableEvent
{
    public function log(Log\Log $log)
    {
        $log->setDescription('Backup queued')
            ->setTarget($this->target)
            ->save()
            ;
    }
}
