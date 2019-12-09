<?php

namespace Packages\Backup\App\Archive\Source;

use Illuminate\Routing\Router;
use App\Http\RouteServiceProvider;

/**
 * Routes regarding Servers.
 */
class SourceRoutesProvider extends RouteServiceProvider {
  /**
   * @var string
   */
  protected $package = 'backup';

  protected function api(Router $router) {
    $router->resource('source', SourceController::class);
  }
}
