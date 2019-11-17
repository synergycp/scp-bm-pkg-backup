<?php

namespace Packages\Backup\App\Archive\Source;

use App\Support\Http\FilterService;
use Illuminate\Database\Eloquent\Builder;

class SourceFilterService extends FilterService {
  /**
   * @var SourceListRequest
   */
  protected $request;

  protected $requestClass = SourceListRequest::class;

  /**
   * @param Builder $query
   *
   * @return Builder
   */
  public function viewable(Builder $query) {
    $this->permission->assertHas(Source::PERMISSION_READ);
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
