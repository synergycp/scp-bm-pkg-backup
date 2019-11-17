<?php

namespace Packages\Backup\App\Archive\Dest\Events;

use App\Log\Log;

class DestCreated extends DestLoggableEvent {
  public function log(Log $log) {
    $log
      ->setDesc('Backup destination created')
      ->setTarget($this->target)
      ->save();
  }
}
