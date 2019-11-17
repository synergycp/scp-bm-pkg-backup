<?php

namespace Packages\Backup\App\Archive\Events;

use App\Log;
use Packages\Backup\App\Archive;

class ArchiveCompleted extends ArchiveLoggableEvent implements
  Archive\Events\ArchiveStatusChangeEvent {
  use HasBackupStatus;

  /**
   * @var int
   */
  protected $status = Archive\ArchiveStatus::FINISHED;

  public function log(Log\Log $log) {
    $log
      ->setDesc('Backup completed')
      ->setTarget($this->target)
      ->save();
  }
}
