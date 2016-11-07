<?php

namespace Packages\Backup\App\Backup\Recurring;

use App\Console\Commands\Command;

class StartRecurringCommand
extends Command
{
    /**
     * @var string
     */
    protected $name = 'pkg:backup:recurring';

    /**
     * @var string
     */
    protected $description = 'Queue recurring backups.';

    /**
     * @var RecurringService
     */
    protected $service;

    /**
     * @param RecurringService $service
     */
    public function __construct(RecurringService $service)
    {
        $this->service = $service;

        parent::__construct();
    }

    /**
     * Run the Command.
     */
    public function handle()
    {
        $this->info(sprintf(
            '%d backups started',
            $this->service->run()
        ));
    }
}
