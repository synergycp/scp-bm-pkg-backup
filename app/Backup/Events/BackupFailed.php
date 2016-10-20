<?php

namespace Packages\Backup\App\Backup\Events;

use Packages\Backup\App\Backup;
use App\Log;

class BackupFailed
extends BackupLoggableEvent
{
    /**
     * @var \Exception
     */
    protected $exc;

    /**
     * @param Backup\Backup $target
     * @param \Exception    $exc
     */
    public function __construct(Backup\Backup $target, \Exception $exc)
    {
        parent::__construct($target);
        $this->exc = $exc;
    }

    public function log(Log\Log $log)
    {
        $log->setDescription('Backup failed')
            ->setTarget($this->target)
            ->setException($this->exception)
            ->save()
            ;
    }
}
