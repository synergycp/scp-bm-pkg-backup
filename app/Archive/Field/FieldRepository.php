<?php

namespace Packages\Backup\App\Archive\Field;

use App\Database\ModelRepository;

class FieldRepository
extends ModelRepository
{
    /**
     * @param Field $item
     */
    public function boot(
        Field $item
    ) {
        $this->setItem($item);
    }
}
