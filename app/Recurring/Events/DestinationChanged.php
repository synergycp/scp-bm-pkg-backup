<?php

namespace Packages\Backup\App\Recurring\Events;

use App\Log;

class DestinationChanged extends RecurringEvent implements Log\LoggableEvent {
  public function log(Log\Log $log) {
    $log
      ->setDesc('Destination changed')
      ->setTarget($this->target)
      ->save();
  }
}
