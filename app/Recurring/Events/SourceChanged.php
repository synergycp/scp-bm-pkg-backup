<?php

namespace Packages\Backup\App\Recurring\Events;

use App\Log;

class SourceChanged extends RecurringEvent implements Log\LoggableEvent {
  public function log(Log\Log $log) {
    $log
      ->setDesc('Source changed')
      ->setTarget($this->target)
      ->save();
  }
}
