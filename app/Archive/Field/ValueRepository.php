<?php

namespace Packages\Backup\App\Archive\Field;

use App\Database\ModelRepository;

class ValueRepository
extends ModelRepository
{
    /**
     * @param Field $item
     */
    public function boot(
        Value $item
    ) {
        $this->setItem($item);
    }
}
