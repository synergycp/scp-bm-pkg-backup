<?php

namespace Packages\Backup\App\Recurring;

use Illuminate\Console\Scheduling\Schedule;
use App\Support\ScheduleServiceProvider;

class RecurringCommandProvider
extends ScheduleServiceProvider
{
    /**
     * @var array
     */
    protected $commands = [
        StartRecurringCommand::class,
    ];

    /**
     * Register the commands.
     */
    public function boot()
    {
        $this->commands($this->commands);

        parent::boot();
    }

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('pkg:backup:recurring')->everyThirtyMinutes();
    }
}
