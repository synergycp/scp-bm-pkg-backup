<?php

namespace Packages\Backup\App\Backup;

use App\Support\EventServiceProvider;
use App\Log\EventLogger;

class BackupEventProvider
extends EventServiceProvider
{
    /**
     * @var array
     */
    protected $listen = [
        Events\BackupCreated::class => [
            EventLogger::class,
            Listeners\BackupStart::class,
        ],
        Events\BackupFailed::class => [
            EventLogger::class,
        ],
        Events\BackupFileCreated::class => [
            EventLogger::class,
            Dest\CopyBackupToDest::class,
        ],
        Dest\CopiedBackupToDest::class => [
            File\DeleteLocal::class,
        ],
    ];
}
