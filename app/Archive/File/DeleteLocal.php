<?php

namespace Packages\Backup\App\Archive\File;

use Packages\Backup\App\Archive;
use Illuminate\Contracts\Queue;

/**
 * Delete the local copy of a Backup after it has been copied to its destination.
 */
class DeleteLocal implements Queue\ShouldQueue {
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
