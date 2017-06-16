<?php

namespace Packages\Backup\App\Archive\Source;

use App\Log\EventLogger;
use App\Support\EventServiceProvider;

class SourceEventProvider extends EventServiceProvider
{
    /**
     * @var array
     */
    protected $listen = [
        Events\SourceCreated::class   => [
            EventLogger::class,
        ],
        Events\SourceDeleted::class   => [
            EventLogger::class,
        ],
        Events\NameChanged::class   => [
            EventLogger::class,
        ],
        Events\HandlerChanged::class   => [
            EventLogger::class,
        ],
    ];
}
