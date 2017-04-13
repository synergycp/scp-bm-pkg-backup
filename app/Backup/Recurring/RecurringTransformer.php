<?php

namespace Packages\Backup\App\Backup\Recurring;

use App\Api\Transformer;

class RecurringTransformer extends Transformer
{
    /**
     * @param Recurring $item
     * @return mixed
     */
    public function item(Recurring $item)
    {
        return $item->expose([
                'id',
                'period',
                'dest',
                'source'
            ]) + [
                'name'       => $item->__toString(),
                'created_at' => $this->date($item->created_at),
                'updated_at' => $this->date($item->updated_at),
            ];
    }

    public function itemPreload($items)
    {
        $items->load('source', 'dest');
    }

    public function date($date)
    {
        if ($date) {
            return ['iso_8601' => $date->toIso8601String(), 'unix' => $date->tz('UTC')->timestamp];
        }

        return;
    }

    public function resource(Recurring $item)
    {
        return $this->item($item) + [];
    }

}