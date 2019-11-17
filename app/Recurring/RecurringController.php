<?php

namespace Packages\Backup\App\Recurring;

use App\Api;

/**
 * Routing for Recurring Archives API Requests.
 */
class RecurringController extends Api\Controller {
  use Api\Traits\CreateResource;
  use Api\Traits\ShowResource;
  use Api\Traits\ListResource;
  use Api\Traits\DeleteResource;
  use Api\Traits\UpdateResource;

  /**
   * @var RecurringRepository
   */
  protected $items;
  /**
   * @var RecurringFilterService
   */
  protected $filter;
  /**
   * @var RecurringUpdateService
   */
  protected $update;
  /**
   * @var RecurringDeleteService
   */
  protected $delete;
  /**
   * @var RecurringTransformer
   */
  protected $transform;

  /**
   * @param RecurringRepository $items
   * @param RecurringFilterService $filter
   * @param RecurringTransformer $transform
   * @param RecurringUpdateService $update
   * @param RecurringDeleteService $delete
   */
  public function boot(
    RecurringRepository $items,
    RecurringFilterService $filter,
    RecurringTransformer $transform,
    RecurringUpdateService $update,
    RecurringDeleteService $delete
  ) {
    $this->items = $items;
    $this->transform = $transform;
    $this->filter = $filter;
    $this->update = $update;
    $this->delete = $delete;
  }

  /**
   * Filter the repository.
   */
  public function filter() {
    $this->items->filter([$this->filter, 'viewable']);
  }
}
