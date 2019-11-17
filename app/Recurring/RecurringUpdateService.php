<?php

namespace Packages\Backup\App\Recurring;

use App\Support\Http\UpdateService;
use Illuminate\Support\Collection;
use Packages\Backup\App\Recurring\Events\DestinationChanged;
use Packages\Backup\App\Recurring\Events\PeriodChanged;
use Packages\Backup\App\Recurring\Events\RecurringCreated;
use Packages\Backup\App\Recurring\Events\SourceChanged;

class RecurringUpdateService extends UpdateService {
  /**
   * @var RecurringFormRequest
   */
  protected $request;

  /**
   * @var string
   */
  protected $requestClass = RecurringFormRequest::class;

  /**
   * @var RecurringService
   */
  protected $recurringService;

  public function boot(RecurringService $recurringService) {
    $this->recurringService = $recurringService;
  }

  public function fillData(Recurring $item) {
    $item->source_id = $this->request->input('source.id');
    $item->destination_id = $this->request->input('dest.id');
    $item->period = $this->request->input('period');
  }

  public function afterCreate(Collection $items) {
    $createEvent = $this->queueHandler(RecurringCreated::class);

    $this->successItems(
      'pkg.backup::recurring.created',
      $items->each($createEvent)
    );

    $this->recurringService->createBackups(
      (new Recurring())->newCollection($items->all())
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

      return;
    }

    $this->setSource($items);
    $this->setDestination($items);
    $this->setPeriod($items);
  }

  public function setSource(Collection $items) {
    $inputs = ['source_id' => $this->input('source.id')];

    $createEvent = $this->queueHandler(SourceChanged::class);

    $this->successItems(
      'pkg.backup::recurring.update.source',
      $items
        ->filter($this->changed($inputs))
        ->reject([$this, 'isCreating'])
        ->each($createEvent)
    );
  }

  public function setDestination(Collection $items) {
    $inputs = ['destination_id' => $this->input('dest.id')];

    $createEvent = $this->queueHandler(DestinationChanged::class);

    $this->successItems(
      'pkg.backup::recurring.update.destination',
      $items
        ->filter($this->changed($inputs))
        ->reject([$this, 'isCreating'])
        ->each($createEvent)
    );
  }

  public function setPeriod(Collection $items) {
    $inputs = ['period' => $this->input('period')];

    $createEvent = $this->queueHandler(PeriodChanged::class);

    $this->successItems(
      'pkg.backup::recurring.update.period',
      $items
        ->filter($this->changed($inputs))
        ->reject([$this, 'isCreating'])
        ->each($createEvent)
    );
  }

  protected function beforeAll(Collection $items) {
    $items->each(function (Recurring $item) {
      $item->assertHasPermissionToEdit();
    });
  }
}
