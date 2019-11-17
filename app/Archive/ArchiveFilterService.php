<?php

namespace Packages\Backup\App\Archive;

use App\Support\Http\FilterService;
use Illuminate\Database\Eloquent\Builder;

class ArchiveFilterService extends FilterService {
  /**
   * @var ArchiveListRequest
   */
  protected $request;

  protected $requestClass = ArchiveListRequest::class;

  /**
   * @param Builder $query
   *
   * @return Builder
   */
  public function viewable(Builder $query) {
    $this->permission->assertHas(Archive::PERMISSION_READ);

    return $query;
  }

  /**
   * @param Builder $query
   *
   * @return Builder
   */
  public function query(Builder $query) {
    $this->prepare()->apply($query);

    if ($this->request->exists('status')) {
      $query->where('status', $this->request->input('status'));
    }

    return $query->orderBy('id', 'desc');
  }
}
