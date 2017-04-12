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
                'name'        => $this->itemSource($item) . ' - ' . $this->itemDestination($item) . ' (' . $this->itemStatus($item) . ')',
                'created_at'  => $this->itemDateFormat($item->created_at),
                'updated_at'  => $this->itemDateFormat($item->updated_at),
                'source'      => $this->itemSource($item),
                'destination' => $this->itemDestination($item),
                'recurring'   => $this->itemRecurring($item),
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

    private function itemRecurring(Archive $item)
    {
        return $item->recurring ? $item->recurring->id : null;
    }

    private function itemDateFormat($dateTime)
    {
        return $dateTime->format('d.m.y H:i');
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