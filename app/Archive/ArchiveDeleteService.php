<?php

namespace Packages\Backup\App\Archive;

use Illuminate\Support\Collection;
use App\Support\Http\DeleteService;
use Packages\Backup\App\Archive\Events\ArchiveDeleted;

class ArchiveDeleteService extends DeleteService {
  /**
   * @param Collection $items
   */
  protected function afterDelete(Collection $items) {
    $this->successItems('pkg.backup::archive.deleted', $items);
  }

  /**
   * @param Archive $item
   *
   * @throws \Exception
   */
  protected function delete($item) {
    $item->assertHasPermissionToDelete();
    $item->delete();
    $this->queue(new ArchiveDeleted($item));
  }
}
