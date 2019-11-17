<?php

namespace Packages\Backup\App\Archive\Dest;

use App\Support\Http\FilterService;
use Illuminate\Database\Eloquent\Builder;

class DestFilterService extends FilterService {
  /**
   * @var DestListRequest
   */
  protected $request;

  protected $requestClass = DestListRequest::class;

  /**
   * @param Builder $query
   *
   * @return Builder
   */
  public function viewable(Builder $query) {
    $this->permission->assertHas(Dest::PERMISSION_READ);

    return $query;
  }

  /**
   * @param Builder $query
   *
   * @return Builder
   */
  public function query(Builder $query) {
    $this->prepare()->apply($query);
    // Filter raw text search
    if ($searchText = $this->request->input('q')) {
      $query->search($this->search->search($searchText));
    }

    return $query;
  }
}
