<?php

namespace Packages\Backup\App\Backup\Recurring\Events;

use App\Log;
use Packages\Backup\App\Backup\Recurring\Recurring;

class RecurringDeleted extends \App\Support\Event implements Log\LoggableEvent
{
    /**
     * @var Recurring
     */
    public $target;

    /**
     * @param Recurring $target
     */
    public function __construct(Recurring $target)
    {
        $this->target = $target;
    }

    public function log(Log\Log $log)
    {
        $log->setDesc('Recurring deleted')
            ->setTarget($this->target)
            ->save();
    }
}