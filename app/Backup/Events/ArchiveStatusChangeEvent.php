<?php

namespace Packages\Backup\App\Backup\Events;

use Packages\Backup\App\Backup;

interface ArchiveStatusChangeEvent
{
    /**
     * @return int
     */
    public function status();

    /**
     * @return Backup\Archive
     */
    public function target();
}
