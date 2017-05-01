<?php

namespace Packages\Backup\App\Backup;

use App\Api\ApiAuthService;
use Illuminate\Support\Collection;
use App\Support\Http\DeleteService;
use Packages\Backup\App\Backup\Events\ArchiveDeleted;

class ArchiveDeleteService extends DeleteService
{
    /**
     * @var ApiAuthService
     */
    protected $auth;

    /**
     * @param ApiAuthService $auth
     */
    public function boot(
        ApiAuthService $auth
    ) {
        $this->auth = $auth;
    }

    /**
     * @param Collection $items
     */
    protected function afterDelete(Collection $items)
    {
        $this->successItems('pkg.backup::backup.archive.deleted', $items);
    }

    /**
     * @param Archive $item
     */
    protected function delete($item)
    {
        $this->checkCanDelete();
        $item->delete();
        $this->queue(new ArchiveDeleted($item));
    }

    protected function checkCanDelete()
    {
        $this->auth->only('admin', 'integration');
    }
}