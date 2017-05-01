<?php

namespace Packages\Backup\App\Recurring\Listeners;

use Packages\Backup\App\Recurring\Events\RecurringDeleted;

class RemoveRecurring
{
    /**
     * Handle recurring delete and remove from archive
     *
     * @param RecurringDeleted $event
     */
    public function handle(RecurringDeleted $event)
    {
        $event->target->archives()->update(['recurring_id' => null]);
    }
}
