<?php

namespace Packages\Backup\App\Configuration\Events;

use App\Log;
use App\Support\Job;
use App\Support\Jobs\IEventWithJob;
use Packages\Backup\App\Configuration\Configuration;
use Packages\Backup\App\Configuration\ConfigurationHealthCheckJob;

class ConfigurationBackupCreated extends \App\Support\Event implements
  Log\LoggableEvent,
  IEventWithJob {
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

  public function job(): Job {
    return new ConfigurationHealthCheckJob();
  }
}
