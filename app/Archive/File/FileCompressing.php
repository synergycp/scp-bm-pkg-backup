<?php

namespace Packages\Backup\App\Archive\File;

use Packages\Backup\App\Archive;

class FileCompressing
extends Archive\Events\ArchiveEvent
implements Archive\Events\ArchiveStatusChangeEvent
{
    use Archive\Events\HasBackupStatus;

    /**
     * @var int
     */
    protected $status = Archive\ArchiveStatus::COMPRESS;
}
