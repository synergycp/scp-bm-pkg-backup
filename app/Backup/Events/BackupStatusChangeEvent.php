<?php

namespace Packages\Backup\App\Backup\Events;

use Packages\Backup\App\Backup;

interface BackupStatusChangeEvent
{
    /**
     * @return int
     */
    public function status();

    /**
     * @return Backup\Backup
     */
    public function target();
}
