<?php

namespace Packages\Backup\App\Backup\Events;

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
