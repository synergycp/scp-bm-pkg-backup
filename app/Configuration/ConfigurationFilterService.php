<?php

namespace Packages\Backup\App\Configuration;

use App\Support\Http\FilterService;
use Illuminate\Database\Eloquent\Builder;

class ConfigurationFilterService extends FilterService {
  /**
   * @var ConfigurationListRequest
   */
  protected $request;

  protected $requestClass = ConfigurationListRequest::class;

  /**
   * @param Builder $query
   *
   * @return Builder
   */
  public function viewable(Builder $query) {
    $this->permission->assertHas(Configuration::PERMISSION_READ);

    return $query;
  }

  /**
   * @param Builder $query
   *
   * @return Builder
   */
  public function query(Builder $query) {
    $this->prepare();
    return $this->request->apply($query);
  }
}
