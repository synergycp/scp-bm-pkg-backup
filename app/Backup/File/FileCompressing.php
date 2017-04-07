<?php

namespace Packages\Backup\App\Backup\File;

use Packages\Backup\App\Backup;

class FileCompressing
extends Backup\Events\BackupEvent
implements Backup\Events\BackupStatusChangeEvent
{
    use Backup\Events\HasBackupStatus;

    /**
     * @var int
     */
    protected $status = Backup\ArchiveStatus::COMPRESS;
}
