<?php

namespace Packages\Backup\App\Recurring\Events;

use App\Log;
use Packages\Backup\App\Recurring\Recurring;

class PeriodChanged extends \App\Support\Event implements Log\LoggableEvent
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
        $log->setDesc('Period changed')
            ->setTarget($this->target)
            ->save();
    }
}
