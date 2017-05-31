<?php

namespace Packages\Backup\App\Archive\Dest\Events;

use App\Log\Log;

class HandlerChanged extends DestinationLoggableEvent
{
    public function log(Log $log)
    {
        $log->setDesc('Destination handler changed')
            ->setTarget($this->target)
            ->save();
    }
}
