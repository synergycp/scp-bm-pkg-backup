<?php

namespace Packages\Backup\App\Backup\File;

use Packages\Backup\App\Backup;

class FileCompressing
extends Backup\Events\ArchiveEvent
implements Backup\Events\ArchiveStatusChangeEvent
{
    use Backup\Events\HasBackupStatus;

    /**
     * @var int
     */
    protected $status = Backup\ArchiveStatus::COMPRESS;
}
