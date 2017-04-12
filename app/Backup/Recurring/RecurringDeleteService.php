<?php

namespace Packages\Backup\App\Backup\Recurring;

use App\Api\ApiAuthService;
use Illuminate\Support\Collection;
use App\Support\Http\DeleteService;

class RecurringDeleteService extends DeleteService
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
        $this->successItems('backup.recurring.deleted', $items);
    }

    /**
     * @param Recurring $item
     */
    protected function delete($item)
    {
        $this->checkCanDelete($item);
        $item->delete();
    }

    /**
     * @param Recurring $item
     */
    protected function checkCanDelete(Recurring $item)
    {
        if ($this->auth->is('admin')) {

        }
    }
}