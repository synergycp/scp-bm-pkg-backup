<?php

namespace Packages\Backup\App\Backup\Dest;

use App\Log;

class CopiedBackupToDest
extends BackupLoggableEvent
{
    public function log(Log\Log $log)
    {
        $log->setDescription('Backup copied to destination')
            ->setTarget($this->target)
            ->save()
            ;
    }
}
