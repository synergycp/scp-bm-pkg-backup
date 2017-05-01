<?php

namespace Packages\Backup\App\Archive;

use App\Api\Transformer;
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
                'recurring_id',
                'dest',
                'source',
            ]) + [
                'name'        => $item->source->name,
                'created_at'  => $this->date($item->created_at),
                'updated_at'  => $this->date($item->updated_at),
                'recurring'   => $item->recurring ? $item->recurring->__toString() : null,
                'status'      => $this->itemStatus($item)
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
