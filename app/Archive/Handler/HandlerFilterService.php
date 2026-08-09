<?php

namespace Packages\Backup\App\Archive\Handler;

use App\Support\Http\FilterService;
use Illuminate\Database\Eloquent\Builder;

class HandlerFilterService extends FilterService {
  /**
   * @var HandlerListRequest
   */
  protected $request;

  protected $requestClass = HandlerListRequest::class;

  /**
   * @param Builder $query
   *
   * @return Builder
   */
  public function viewable(Builder $query) {
    $this->permission->assertHas(Handler::PERMISSION_READ);

    return $query;
  }

  /**
   * @param Builder $query
   *
   * @return Builder
   */
  public function query(Builder $query) {
    $this->prepare()->apply($query);

    // Filter by handler type (HandlerType::SOURCE / HandlerType::DEST) so
    // forms can list only the handlers that fit what they are creating.
    if ($this->request->exists('type')) {
      $query->where('type', (int) $this->request->input('type'));
    }

    // Filter raw text search
    if ($searchText = $this->request->input('q')) {
      $query->search($this->search->search($searchText));
    }

    return $query;
  }
}
