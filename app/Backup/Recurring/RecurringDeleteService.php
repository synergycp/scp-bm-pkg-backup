<?php

namespace Packages\Backup\App\Backup\Recurring;

use App\Api\ApiAuthService;
use Illuminate\Support\Collection;
use App\Support\Http\DeleteService;
use Packages\Backup\App\Backup\Recurring\Events\RecurringDeleted;

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
        $this->successItems('pkg.backup::backup.recurring.deleted', $items);
    }

    /**
     * @param Recurring $item
     */
    protected function delete($item)
    {
        $this->checkCanDelete($item);

        event(new RecurringDeleted($item));

        $item->delete();
    }

    protected function checkCanDelete()
    {
        $this->auth->only('admin', 'integration');
    }
}