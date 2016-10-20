<?php

namespace Packages\Backup\App\Backup\File;

use Packages\Backup\App\Backup;
use App\Log;

class FileCreated
extends Backup\Events\BackupLoggableEvent
{
    public function log(Log\Log $log)
    {
        $log->setDesc('Backup saved to file')
            ->setTarget($this->target)
            ->save()
            ;
    }
}
