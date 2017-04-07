<?php

namespace Packages\Backup\App\Backup;

use Illuminate\Support\Collection;
use App\Support\Http\DeleteService;
use App\Api\ApiAuthService;

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
        $this->successItems('backup.deleted', $items);
    }

    /**
     * @param _STUB_ $item
     */
    protected function delete($item)
    {
        $this->checkCanDelete($item);
        $item->delete();
//        $this->queue(new Events\_STUB_Deleted($item));
    }

    /**
     * @param _STUB_ $item
     */
    protected function checkCanDelete(Archive $item)
    {
        if ($this->auth->is('admin')) {
        }
    }
}