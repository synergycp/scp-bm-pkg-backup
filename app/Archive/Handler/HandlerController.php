<?php

namespace Packages\Backup\App\Archive\Handler;

use App\Api;

/**
 * Routing for Handler API Requests.
 */
class HandlerController extends Api\Controller {
  use Api\Traits\ShowResource;
  use Api\Traits\ListResource;
  use Api\Traits\DeleteResource;
  use Api\Traits\UpdateResource;

  /**
   * @var HandlerRepository
   */
  protected $items;
  /**
   * @var HandlerFilterService
   */
  protected $filter;

  /**
   * @var HandlerTransformer
   */
  protected $transform;

  /**
   * @param HandlerRepository    $items
   * @param HandlerFilterService $filter
   * @param HandlerTransformer   $transform
   */
  public function boot(
    HandlerRepository $items,
    HandlerFilterService $filter,
    HandlerTransformer $transform
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
