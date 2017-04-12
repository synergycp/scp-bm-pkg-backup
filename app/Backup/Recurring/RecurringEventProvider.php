<?php

namespace Packages\Backup\App\Backup\Recurring;

use App\Log\EventLogger;
use App\Support\EventServiceProvider;
use Packages\Backup\App\Backup\Recurring\Events\RecurringCreated;
use Packages\Backup\App\Backup\Recurring\Events\RecurringDeleted;

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
    ];
}
