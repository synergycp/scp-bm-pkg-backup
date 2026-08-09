<?php

namespace Packages\Backup\App\Archive\File;

use Packages\Backup\App\Archive;

/**
 * Delete the local copy of a Backup after it has been copied to its destination.
 *
 * DO NOT QUEUE THIS: the temp file only exists on the worker that created the
 * backup, so it must run on that same worker.
 */
class DeleteLocal {
  /**
   * @var FileService
   */
  protected $file;

  /**
   * @param FileService $file
   */
  public function __construct(FileService $file) {
    $this->file = $file;
  }

  /**
   * @param Archive\Events\ArchiveEvent $event
   */
  public function handle(Archive\Events\ArchiveEvent $event) {
    $this->file->delete($event->target);
  }
}
