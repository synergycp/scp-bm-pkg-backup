<?php

namespace Packages\Backup\App\Recurring\Events;

use App\Log;

class RecurringCreated extends RecurringEvent implements Log\LoggableEvent {
  public function log(Log\Log $log) {
    $log
      ->setDesc('New recurring created')
      ->setTarget($this->target)
      ->save();
  }
}
