<?php

namespace Packages\Backup\App\Archive;

use App\Support\Http\UpdateService;
use Illuminate\Support\Collection;

class ArchiveUpdateService extends UpdateService {
  /**
   * @var ArchiveFormRequest
   */
  protected $request;

  /**
   * @var string
   */
  protected $requestClass = ArchiveFormRequest::class;

  /**
   * @param Archive $item
   */
  public function fillData(Archive $item) {
    $item->source_id = $this->request->input('source.id');
    $item->destination_id = $this->request->input('destination.id');
    $item->recurring_id = $this->request->input('recurring.id');
  }

  /**
   * @param Collection $items
   */
  public function afterCreate(Collection $items) {
    $createEvent = $this->queueHandler(Events\ArchiveCreated::class);

    $this->successItems(
      'pkg.backup::archive.created',
      $items->each($createEvent)
    );
  }

  /**
   * Update all archives using the given request.
   *
   * @param Collection $items
   */
  public function updateAll(Collection $items) {
    if ($this->create) {
      $items->map([$this, 'fillData']);
    }
  }

  protected function beforeAll(Collection $items) {
    $items->each(function (Archive $item) {
      $item->assertHasPermissionToEdit();
    });
  }
}
