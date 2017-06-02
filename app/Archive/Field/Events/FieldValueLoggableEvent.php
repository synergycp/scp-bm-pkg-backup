<?php

namespace Packages\Backup\App\Archive\Field\Events;

use App\Log;

abstract class FieldValueLoggableEvent extends FieldValueEvent implements Log\LoggableEvent
{
    abstract public function log(Log\Log $log);
}
