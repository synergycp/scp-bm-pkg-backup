<?php

namespace Packages\Backup\App\Backup\Events;

use Packages\Backup\App\Backup;
use App\Log;

class BackupFailed
extends BackupLoggableEvent
implements Backup\Events\BackupStatusChangeEvent
{
    use HasBackupStatus;

    /**
     * @var int
     */
    protected $status = Backup\ArchiveStatus::FAILED;

    /**
     * @var \Exception
     */
    protected $exc;

    /**
     * @param Backup\Archive $target
     * @param \Exception    $exc
     */
    public function __construct(
        Backup\Archive $target,
        \Exception $exc
    ) {
        parent::__construct($target);

        $this->exc = $exc;
    }

    public function log(Log\Log $log)
    {
        $log->setDesc('Backup failed')
            ->setTarget($this->target)
            ->setException($this->exc)
            ->save()
            ;
    }
}
