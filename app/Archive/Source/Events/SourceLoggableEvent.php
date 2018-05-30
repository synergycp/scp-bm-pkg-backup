<?php

namespace Packages\Backup\App\Archive\Source\Events;

use App\Log;

abstract class SourceLoggableEvent extends SourceEvent implements Log\LoggableEvent
{
    abstract public function log(Log\Log $log);
}
