<?php

namespace Packages\Backup\App\Recurring;

use App\Support\Http\DeleteService;
use Illuminate\Support\Collection;
use Packages\Backup\App\Recurring\Events\RecurringDeleted;

class RecurringDeleteService extends DeleteService {
  /**
   * @param Collection $items
   */
  protected function afterDelete(Collection $items) {
    $this->successItems('pkg.backup::recurring.deleted', $items);
  }

  /**
   * @param Recurring $item
   *
   * @throws \Exception
   */
  protected function delete($item) {
    $item->assertHasPermissionToDelete();

    event(new RecurringDeleted($item));

    $item->delete();
  }
}
