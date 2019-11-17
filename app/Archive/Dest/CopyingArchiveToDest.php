<?php

namespace Packages\Backup\App\Archive\Dest;

use Packages\Backup\App\Archive;
use App\Log;

class CopyingArchiveToDest
  extends Archive\Events\ArchiveLoggableEvent
  implements Archive\Events\ArchiveStatusChangeEvent {
  use Archive\Events\HasBackupStatus;

  /**
   * @var int
   */
  protected $status = Archive\ArchiveStatus::COPYING;

  public function log(Log\Log $log) {
    $log
      ->setDesc('Copying backup to destination')
      ->setTarget($this->target)
      ->save();
  }
}
