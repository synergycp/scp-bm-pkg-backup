<?php

namespace Packages\Backup\App\Recurring\Events;

use App\Log;

class MaxArchivesChanged extends RecurringEvent implements Log\LoggableEvent {
  public function log(Log\Log $log) {
    $log
      ->setDesc('Retention limit changed')
      ->setTarget($this->target)
      ->save();
  }
}
