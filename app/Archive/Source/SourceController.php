<?php

namespace Packages\Backup\App\Archive\Source;

use App\Api;

/**
 * Routing for Archive Source API Requests.
 */
class SourceController extends Api\Controller {
  use Api\Traits\ShowResource;
  use Api\Traits\ListResource;
  use Api\Traits\DeleteResource;
  use Api\Traits\UpdateResource;

  /**
   * @var SourceRepository
   */
  protected $items;
  /**
   * @var SourceFilterService
   */
  protected $filter;

  /**
   * @var SourceTransformer
   */
  protected $transform;

  /**
   * @param SourceRepository $items
   * @param SourceFilterService $filter
   * @param SourceTransformer $transform
   */
  public function boot(
    SourceRepository $items,
    SourceFilterService $filter,
    SourceTransformer $transform
  ) {
    $this->items = $items;
    $this->transform = $transform;
    $this->filter = $filter;
  }

  /**
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function filter() {
    return $this->filter->viewable($this->items->query());
  }
}
