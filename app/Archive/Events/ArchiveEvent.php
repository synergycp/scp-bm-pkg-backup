<?php

namespace Packages\Backup\App\Archive\Events;

use Packages\Backup\App\Archive;

abstract
class ArchiveEvent
extends \App\Support\Event
{
    /**
     * @var Archive\Archive
     */
    public $target;

    /**
     * @param Archive\Archive $target
     */
    public function __construct(Backup\Archive $target)
    {
        $this->target = $target;
    }
}
