<?php

namespace Packages\Backup\App\Archive\Source\Events;

use App\Log\Log;

class SourceCreated extends SourceLoggableEvent
{
    public function log(Log $log)
    {
        $log->setDesc('Backup source created')
            ->setTarget($this->target)
            ->save();
    }
}
