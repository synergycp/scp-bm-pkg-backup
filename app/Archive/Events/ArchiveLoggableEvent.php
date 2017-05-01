<?php

namespace Packages\Backup\App\Archive\Events;

use App\Log;

abstract
class ArchiveLoggableEvent
extends ArchiveEvent
implements Log\LoggableEvent
{
    abstract public function log(Log\Log $log);
}
