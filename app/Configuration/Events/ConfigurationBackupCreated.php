<?php

namespace Packages\Backup\App\Configuration\Events;

use App\Log;
use Packages\Backup\App\Configuration\Configuration;

class ConfigurationBackupCreated extends \App\Support\Event implements
  Log\LoggableEvent {
  /**
   * @var Configuration
   */
  public $target;

  /**
   * @param Configuration $target
   */
  public function __construct(Configuration $target) {
    $this->target = $target;
  }

  public function log(Log\Log $log) {
    $log
      ->setDesc('New configuration backup saved')
      ->setTarget($this->target)
      ->save();
  }
}
