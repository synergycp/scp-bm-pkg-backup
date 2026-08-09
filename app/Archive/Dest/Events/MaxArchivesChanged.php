<?php

namespace Packages\Backup\App\Archive\Dest\Events;

use App\Log\Log;

class MaxArchivesChanged extends DestLoggableEvent {
  public function log(Log $log) {
    $log
      ->setDesc('Destination retention limit changed')
      ->setTarget($this->target)
      ->save();
  }
}
