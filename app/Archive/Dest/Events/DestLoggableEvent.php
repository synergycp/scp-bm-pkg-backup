<?php

namespace Packages\Backup\App\Archive\Dest\Events;

use App\Log;

abstract class DestLoggableEvent extends DestEvent implements Log\LoggableEvent
{
    abstract public function log(Log\Log $log);
}
