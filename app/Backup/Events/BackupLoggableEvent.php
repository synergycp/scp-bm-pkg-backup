<?php

namespace Packages\Backup\App\Backup\Events;

use App\Log;

abstract
class BackupLoggableEvent
extends BackupEvent
implements Log\LoggableEvent
{
    abstract public function log(Log\Log $log);
}
