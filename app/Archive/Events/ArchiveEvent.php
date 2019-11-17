<?php

namespace Packages\Backup\App\Archive\Events;

use App\Support\Job;
use App\Support\Jobs\IEventWithJob;
use Packages\Backup\App\Archive;

abstract class ArchiveEvent extends \App\Support\Event implements
  IEventWithJob {
  /**
   * @var Archive\Archive
   */
  public $target;

  /**
   * @param Archive\Archive $target
   */
  public function __construct(Archive\Archive $target) {
    $this->target = $target;
  }

  public function job(): Job {
    return new Archive\ArchiveHealthCheckJob();
  }
}
