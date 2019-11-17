<?php

namespace Packages\Backup\App\Archive;

use App\Support\EventServiceProvider;
use App\Log\EventLogger;
use App\Support\Jobs\JobRunnerEventListener;

class ArchiveEventProvider extends EventServiceProvider {
  /**
   * @var array
   */
  protected $listen = [
    Events\ArchiveCreated::class => [
      EventLogger::class,
      File\BackupToFile::class,
      JobRunnerEventListener::class,
    ],
    Events\ArchiveCompleted::class => [
      EventLogger::class,
      MarkArchiveStatus::class,
      JobRunnerEventListener::class,
    ],
    Events\ArchiveFailed::class => [
      EventLogger::class,
      MarkArchiveStatus::class,
      JobRunnerEventListener::class,
    ],
    Events\ArchiveDeleted::class => [
      EventLogger::class,
      JobRunnerEventListener::class,
    ],

    File\FileCompressing::class => [MarkArchiveStatus::class],
    File\FileCreated::class => [Dest\CopyBackupToDest::class],
    File\FileDeleted::class => [FireArchiveCompleted::class],

    Dest\CopyingArchiveToDest::class => [
      EventLogger::class,
      MarkArchiveStatus::class,
    ],
    Dest\CopiedArchiveToDest::class => [File\DeleteLocal::class],
  ];
}
