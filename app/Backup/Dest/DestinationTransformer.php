<?php

namespace Packages\Backup\App\Backup\Dest;

use App\Api\Transformer;

class DestinationTransformer extends Transformer
{
    /**
     * @param Dest $item
     * @return mixed
     */
    public function item(Dest $item)
    {
        return $item->expose(['id', 'name']);
    }

    public function resource(Dest $item)
    {
        return $this->item($item) + [];
    }

}