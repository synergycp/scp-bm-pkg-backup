<?php

namespace Packages\Backup\App\Archive\Dest;

use App\Log\EventLogger;
use App\Support\EventServiceProvider;

class DestEventProvider extends EventServiceProvider
{
    /**
     * @var array
     */
    protected $listen = [
        Events\DestCreated::class   => [
            EventLogger::class,
        ],
        Events\DestDeleted::class   => [
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
