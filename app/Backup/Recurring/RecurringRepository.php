<?php

namespace Packages\Backup\App\Backup\Recurring;

use App\Database\ModelRepository;

class RecurringRepository
    extends ModelRepository
{
    /**
     * @param Recurring $item
     */
    public function boot(Recurring $item)
    {
        $this->setItem($item);
    }
}
