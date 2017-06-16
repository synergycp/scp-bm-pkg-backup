<?php

namespace Packages\Backup\App\Archive\Source\Events;

use App\Log\Log;

class HandlerChanged extends SourceLoggableEvent
{
    public function log(Log $log)
    {
        $log->setDesc('Source handler changed')
            ->setTarget($this->target)
            ->save();
    }
}
