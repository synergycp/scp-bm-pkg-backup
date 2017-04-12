<?php

namespace Packages\Backup\App\Backup;

use App\Api\Transformer;
use Illuminate\Support\Collection;
use App\Server\ServerFilterService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Packages\Abuse\App\Report\Comment\CommentTransformer;

class ArchiveTransformer extends Transformer
{
    /**
     * @param Archive $item
     * @return mixed
     */
    public function item(Archive $item)
    {
        return $item->expose([
                'id',
            ]) + [
                'name'        => $this->itemSource($item),
                'created_at'  => $this->date($item->created_at),
                'updated_at'  => $this->date($item->updated_at),
                'source'      => $this->itemSource($item),
                'destination' => $this->itemDestination($item),
                'recurring'   => $item->recurring->__toString(),
                'status'      => $this->itemStatus($item)
            ];
    }

    public function itemPreload($items)
    {
        $items->load('source.handler', 'dest.handler');
    }

    private function itemSource(Archive $item)
    {
        return $item->source ? $item->source->name : null;
    }

    private function itemDestination(Archive $item)
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

    private function itemStatus(Archive $item)
    {
        switch ($item->status) {
            case 0:
                return 'QUEUED';
                break;
            case 1:
                return 'COMPRESS';
                break;
            case 2:
                return 'COPYING';
                break;
            case 4:
                return 'FINISHED';
                break;
            case 10:
                return 'FAILED';
                break;
        }
    }

    public function resource(Archive $item)
    {
        return $this->item($item) + [];
    }

}