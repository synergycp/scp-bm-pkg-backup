<?php

namespace Packages\Backup\App\Archive\Field;

use App\Api;

/**
 * Routing for Archive Fields API Requests.
 */
class FieldController extends Api\Controller {
  use Api\Traits\ShowResource;
  use Api\Traits\ListResource;

  /**
   * @var FieldRepository
   */
  protected $items;
  /**
   * @var FieldFilterService
   */
  protected $filter;

  /**
   * @var FieldTransformer
   */
  protected $transform;

  /**
   * @param FieldRepository $items
   * @param FieldFilterService $filter
   * @param FieldTransformer $transform
   */
  public function boot(
    FieldRepository $items,
    FieldFilterService $filter,
    FieldTransformer $transform
  ) {
    $this->items = $items;
    $this->transform = $transform;
    $this->filter = $filter;
  }

  /**
   * Filter the repository.
   *
   * @param string|null $handlerId
   *
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function filter($handlerId = null) {
    $query = $this->filter->viewable($this->items->query());

    if ($handlerId) {
      $query->where('pkg_backup_fields.handler_id', $handlerId);
    }

    return $query;
  }
}
