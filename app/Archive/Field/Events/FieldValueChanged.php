<?php

namespace Packages\Backup\App\Archive\Field\Events;

use App\Log\Log;

class FieldValueChanged extends FieldValueLoggableEvent {
  public function log(Log $log) {
    $log
      ->setDesc('Field value changed')
      ->setTarget($this->target)
      ->save();
  }
}
