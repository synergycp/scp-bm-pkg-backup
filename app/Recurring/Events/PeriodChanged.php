<?php

namespace Packages\Backup\App\Recurring\Events;

use App\Log;

class PeriodChanged extends RecurringEvent implements Log\LoggableEvent {
  public function log(Log\Log $log) {
    $log
      ->setDesc('Period changed')
      ->setTarget($this->target)
      ->save();
  }
}
