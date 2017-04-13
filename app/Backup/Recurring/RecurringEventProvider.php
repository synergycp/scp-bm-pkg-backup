<?php

namespace Packages\Backup\App\Backup\Recurring;

use App\Log\EventLogger;
use App\Support\EventServiceProvider;
use Packages\Backup\App\Backup\Recurring\Events\DestinationChanged;
use Packages\Backup\App\Backup\Recurring\Events\PeriodChanged;
use Packages\Backup\App\Backup\Recurring\Events\RecurringCreated;
use Packages\Backup\App\Backup\Recurring\Events\RecurringDeleted;
use Packages\Backup\App\Backup\Recurring\Events\SourceChanged;

class RecurringEventProvider
    extends EventServiceProvider
{
    /**
     * @var array
     */
    protected $listen = [
        RecurringCreated::class => [
            EventLogger::class,
        ],
        RecurringDeleted::class => [
            EventLogger::class,
        ],
        SourceChanged::class => [
            EventLogger::class,
        ],
        DestinationChanged::class => [
            EventLogger::class,
        ],
        PeriodChanged::class => [
            EventLogger::class,
        ],
    ];
}
