<?php

namespace Packages\Backup\App\Backup\Events;

use Packages\Backup\App\Backup;

abstract
class BackupEvent
extends \App\Support\Event
{
    /**
     * @var Backup\Backup
     */
    protected $target;

    /**
     * @param Backup\Backup $target
     */
    public function __construct(Backup\Backup $target)
    {
        $this->target = $target;
    }
}
