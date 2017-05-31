<?php

namespace Packages\Backup\App\Archive\Dest\Events;

use App\Log\Log;

class NameChanged extends DestinationLoggableEvent
{
    public function log(Log $log)
    {
        $log->setDesc('Destination name changed')
            ->setTarget($this->target)
            ->save();
    }
}
