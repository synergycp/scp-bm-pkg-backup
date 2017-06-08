<?php

namespace Packages\Backup\App\Archive\Dest;

use App\Api\ApiAuthService;
use Illuminate\Support\Collection;
use App\Support\Http\DeleteService;

class DestDeleteService extends DeleteService
{
    /**
     * @param Collection $items
     */
    protected function afterDelete(Collection $items)
    {
        $this->successItems('pkg.backup::destination.deleted', $items);
    }

    /**
     * @param Archive $item
     */
    protected function delete($item)
    {
        $this->checkCanDelete();
        $item->delete();
        $this->queue(new Events\DestDeleted($item));
    }

    protected function checkCanDelete()
    {
        $this->auth->only('admin', 'integration');
    }
}