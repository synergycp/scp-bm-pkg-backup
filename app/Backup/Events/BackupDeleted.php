<?php

namespace Packages\Backup\App\Backup\Events;

use App\Log;

class BackupDeleted
extends BackupLoggableEvent
{
    public function log(Log\Log $log)
    {
        $log->setDesc('Backup deleted')
            ->setTarget($this->target)
            ->save()
            ;
    }
}
