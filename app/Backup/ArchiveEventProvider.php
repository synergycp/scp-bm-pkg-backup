<?php

namespace Packages\Backup\App\Backup;

use App\Support\EventServiceProvider;
use App\Log\EventLogger;

class ArchiveEventProvider
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
            MarkArchiveStatus::class,
        ],
        Events\BackupFailed::class => [
            EventLogger::class,
            MarkArchiveStatus::class,
        ],

        File\FileCompressing::class => [
            MarkArchiveStatus::class,
        ],
        File\FileCreated::class => [
            Dest\CopyBackupToDest::class,
        ],
        File\FileDeleted::class => [
            FireArchiveCompleted::class,
        ],

        Dest\CopyingBackupToDest::class => [
            EventLogger::class,
            MarkArchiveStatus::class,
        ],
        Dest\CopiedBackupToDest::class => [
            File\DeleteLocal::class,
        ],
    ];
}
