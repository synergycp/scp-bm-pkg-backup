<?php

namespace Packages\Backup\App\Archive\Dest;

use App\Support\Http\DeleteService;
use Illuminate\Support\Collection;

class DestDeleteService extends DeleteService {
  /**
   * @param Collection $items
   */
  protected function afterDelete(Collection $items) {
    $this->successItems('pkg.backup::destination.deleted', $items);
  }

  /**
   * @param Dest $item
   *
   * @throws \Exception
   */
  protected function delete($item) {
    $item->assertHasPermissionToDelete();
    $item->delete();
    $this->queue(new Events\DestDeleted($item));
  }
}
