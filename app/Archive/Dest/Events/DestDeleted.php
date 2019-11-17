<?php

namespace Packages\Backup\App\Archive\Dest\Events;

use App\Log\Log;

class DestDeleted extends DestLoggableEvent {
  public function log(Log $log) {
    $log
      ->setDesc('Backup destination deleted')
      ->setTarget($this->target)
      ->save();
  }
}
