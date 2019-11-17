<?php

namespace Packages\Backup\App\Configuration;

use Illuminate\Routing\Router;
use App\Http\RouteServiceProvider;

/**
 * Routes regarding Servers.
 */
class ConfigurationRoutesProvider extends RouteServiceProvider {
  /**
   * @var string
   */
  protected $package = 'backup';

  protected function api(Router $router) {
    $router->resource('configuration', ConfigurationController::class, [
      'only' => ['index', 'store', 'show'],
    ]);
  }
}
