<?php

namespace Packages\Backup\App\Recurring\Events;

use App\Log;

class RecurringDeleted extends RecurringEvent implements Log\LoggableEvent {
  public function log(Log\Log $log) {
    $log
      ->setDesc('Recurring deleted')
      ->setTarget($this->target)
      ->save();
  }
}
