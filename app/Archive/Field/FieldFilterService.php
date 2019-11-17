<?php

namespace Packages\Backup\App\Archive\Field;

use App\Support\Http\FilterService;
use Illuminate\Database\Eloquent\Builder;

class FieldFilterService extends FilterService {
  /**
   * @var FieldListRequest
   */
  protected $request;

  protected $requestClass = FieldListRequest::class;

  /**
   * @param Builder $query
   *
   * @return Builder
   */
  public function viewable(Builder $query) {
    $this->permission->assertHas(Field::PERMISSION_READ);

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
