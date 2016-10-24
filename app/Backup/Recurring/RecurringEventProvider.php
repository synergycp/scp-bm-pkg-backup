<?php

namespace Packages\Backup\App\Backup\Recurring;

use App\Support\EventServiceProvider;
use App\Log\EventLogger;

class RecurringEventProvider
extends EventServiceProvider
{
    /**
     * @var array
     */
    protected $listen = [
    ];
}
