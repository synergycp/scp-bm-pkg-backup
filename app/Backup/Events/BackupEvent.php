<?php

namespace Packages\Backup\App\Backup\Events;

use Packages\Backup\App\Backup;

abstract
class BackupEvent
extends \App\Support\Event
{
    /**
     * @var Backup\Archive
     */
    public $target;

    /**
     * @param Backup\Archive $target
     */
    public function __construct(Backup\Archive $target)
    {
        $this->target = $target;
    }
}
