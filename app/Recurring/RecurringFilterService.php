<?php

namespace Packages\Backup\App\Recurring;

use App\Support\Http\FilterService;
use Illuminate\Database\Eloquent\Builder;

class RecurringFilterService extends FilterService {
  /**
   * @var RecurringListRequest
   */
  protected $request;

  protected $requestClass = RecurringListRequest::class;

  /**
   * @param Builder $query
   *
   * @return Builder
   */
  public function viewable(Builder $query) {
    $this->permission->assertHas(Recurring::PERMISSION_READ);

    return $query;
  }

  /**
   * @param Builder $query
   *
   * @return Builder
   */
  public function query(Builder $query) {
    return $query;
  }
}
