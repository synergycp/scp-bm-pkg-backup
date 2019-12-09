<?php

namespace Packages\Backup\App\Archive;

use Illuminate\Routing\Router;
use App\Http\RouteServiceProvider;
use Packages\Backup\App\Recurring\RecurringController;

/**
 * Routes regarding Servers.
 */
class ArchiveRoutesProvider extends RouteServiceProvider {
  /**
   * @var string
   */
  protected $package = 'backup';

  protected function api(Router $router) {
    $router->resource('archive', ArchiveController::class);
    $router->resource('recurring', RecurringController::class);
  }
}
