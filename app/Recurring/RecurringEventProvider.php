<?php

namespace Packages\Backup\App\Recurring;

use App\Log\EventLogger;
use App\Support\EventServiceProvider;
use App\Support\Jobs\JobRunnerEventListener;
use Packages\Backup\App\Recurring\Events\DestinationChanged;
use Packages\Backup\App\Recurring\Events\PeriodChanged;
use Packages\Backup\App\Recurring\Events\RecurringCreated;
use Packages\Backup\App\Recurring\Events\RecurringDeleted;
use Packages\Backup\App\Recurring\Events\SourceChanged;
use Packages\Backup\App\Recurring\Listeners\RemoveRecurring;

class RecurringEventProvider extends EventServiceProvider {
  /**
   * @var array
   */
  protected $listen = [
    RecurringCreated::class => [
      EventLogger::class,
      JobRunnerEventListener::class,
    ],
    RecurringDeleted::class => [
      EventLogger::class,
      RemoveRecurring::class,
      JobRunnerEventListener::class,
    ],
    SourceChanged::class => [EventLogger::class],
    DestinationChanged::class => [EventLogger::class],
    PeriodChanged::class => [EventLogger::class],
  ];
}
