<?php

namespace Packages\Backup\App\Archive\Dest\Events;

use App\Log\Log;

class DestinationCreated extends DestinationLoggableEvent
{
    public function log(Log $log)
    {
        $log->setDesc('Backup destination created')
            ->setTarget($this->target)
            ->save();
    }
}
