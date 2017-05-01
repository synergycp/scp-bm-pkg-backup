<?php

namespace Packages\Backup\App\Archive\Handler;

use App\Api\Transformer;

class HandlerTransformer extends Transformer
{
    /**
     * @param Handler $item
     * @return mixed
     */
    public function item(Handler $item)
    {
        return $item->expose(['id', 'name', 'class', 'type']);
    }

    public function resource(Handler $item)
    {
        return $this->item($item) + [];
    }

}
