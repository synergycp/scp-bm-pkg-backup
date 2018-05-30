<?php

namespace Packages\Backup\App\Archive\Source;

use App\Api\ApiAuthService;
use Illuminate\Support\Collection;
use App\Support\Http\DeleteService;

class SourceDeleteService extends DeleteService
{
    /**
     * @param Collection $items
     */
    protected function afterDelete(Collection $items)
    {
        $this->successItems('pkg.backup::source.deleted', $items);
    }

    /**
     * @param Archive $item
     */
    protected function delete($item)
    {
        $this->checkCanDelete();
        $item->delete();
        $this->queue(new Events\SourceDeleted($item));
    }

    protected function checkCanDelete()
    {
        $this->auth->only('admin', 'integration');
    }
}
