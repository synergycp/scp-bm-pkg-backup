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
        Events\ArchiveCreated::class   => [
            EventLogger::class,
            File\BackupToFile::class,
        ],
        Events\ArchiveCompleted::class => [
            EventLogger::class,
            MarkArchiveStatus::class,
        ],
        Events\ArchiveFailed::class    => [
            EventLogger::class,
            MarkArchiveStatus::class,
        ],
        Events\ArchiveDeleted::class   => [
            EventLogger::class,
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

        Dest\CopyingArchiveToDest::class => [
            EventLogger::class,
            MarkArchiveStatus::class,
        ],
        Dest\CopiedArchiveToDest::class  => [
            File\DeleteLocal::class,
        ],
    ];
}
