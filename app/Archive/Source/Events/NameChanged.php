<?php

namespace Packages\Backup\App\Archive\Source\Events;

use App\Log\Log;

class NameChanged extends SourceLoggableEvent
{
    public function log(Log $log)
    {
        $log->setDesc('Source name changed')
            ->setTarget($this->target)
            ->save();
    }
}
