<?php

namespace Packages\Backup\App\Archive\Events;

use App\Log;

class ArchiveCreated
extends ArchiveLoggableEvent
{
    public function log(Log\Log $log)
    {
        $log->setDesc('Backup queued')
            ->setTarget($this->target)
            ->save()
            ;
    }
}
