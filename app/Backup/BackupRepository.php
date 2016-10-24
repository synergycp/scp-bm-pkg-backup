<?php

namespace Packages\Backup\App\Backup;

use App\Database\ModelRepository;

class BackupRepository
extends ModelRepository
{
    /**
     * @param Backup $item
     */
    public function boot(
        Backup $item
    ) {
        $this->setItem($item);
    }
}
