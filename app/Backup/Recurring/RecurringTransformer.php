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
                'period'
            ]) + [
                'name'        => $item->__toString(),
                'created_at'  => $this->date($item->created_at),
                'updated_at'  => $this->date($item->updated_at),
                'source'      => $this->itemSource($item),
                'destination' => $this->itemDestination($item),
            ];
    }

    public function itemPreload($items)
    {
        $items->load('source.handler', 'dest.handler');
    }

    private function itemSource(Recurring $item)
    {
        return $item->source ? $item->source->name : null;
    }

    private function itemDestination(Recurring $item)
    {
        return $item->dest ? $item->dest->name : null;
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