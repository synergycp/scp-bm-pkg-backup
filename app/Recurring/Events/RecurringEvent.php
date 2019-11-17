<?php

namespace Packages\Backup\App\Recurring\Events;

use App\Support\Job;
use App\Support\Jobs\IEventWithJob;
use Packages\Backup\App\Recurring\Recurring;
use Packages\Backup\App\Recurring\RecurringHealthCheckJob;

abstract class RecurringEvent implements IEventWithJob {
  /**
   * @var Recurring
   */
  public $target;

  /**
   * @param Recurring $target
   */
  public function __construct(Recurring $target) {
    $this->target = $target;
  }

  public function job(): Job {
    return new RecurringHealthCheckJob();
  }
}
