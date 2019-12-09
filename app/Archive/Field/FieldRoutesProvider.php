<?php

namespace Packages\Backup\App\Archive\Field;

use Illuminate\Routing\Router;
use App\Http\RouteServiceProvider;

/**
 * Routes regarding Servers.
 */
class FieldRoutesProvider extends RouteServiceProvider {
  /**
   * @var string
   */
  protected $package = 'backup';

  protected function api(Router $router) {
    $router->resource('field/{handler?}', FieldController::class, [
      'only' => ['index'],
    ]);
  }
}
