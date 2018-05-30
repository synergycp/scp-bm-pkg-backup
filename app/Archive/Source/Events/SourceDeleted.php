<?php

namespace Packages\Backup\App\Archive\Source\Events;

use App\Log\Log;

class SourceDeleted extends SourceLoggableEvent
{
    public function log(Log $log)
    {
        $log->setDesc('Backup source deleted')
            ->setTarget($this->target)
            ->save();
    }
}
