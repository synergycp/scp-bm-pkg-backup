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
            File\BackupToFile::class,
        ],
        Events\BackupCompleted::class => [
            EventLogger::class,
            MarkBackupStatus::class,
        ],
        Events\BackupFailed::class => [
            EventLogger::class,
            MarkBackupStatus::class,
        ],

        File\FileCompressing::class => [
            MarkBackupStatus::class,
        ],
        File\FileCreated::class => [
            Dest\CopyBackupToDest::class,
        ],
        File\FileDeleted::class => [
            FireBackupCompleted::class,
        ],

        Dest\CopyingBackupToDest::class => [
            EventLogger::class,
            MarkBackupStatus::class,
        ],
        Dest\CopiedBackupToDest::class => [
            File\DeleteLocal::class,
        ],
    ];
}
