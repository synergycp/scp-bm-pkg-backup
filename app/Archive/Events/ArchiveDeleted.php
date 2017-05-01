<?php

namespace Packages\Backup\App\Archive\Events;

use App\Log;

class ArchiveDeleted
extends ArchiveLoggableEvent
{
    public function log(Log\Log $log)
    {
        $log->setDesc('Backup deleted')
            ->setTarget($this->target)
            ->save()
            ;
    }
}
