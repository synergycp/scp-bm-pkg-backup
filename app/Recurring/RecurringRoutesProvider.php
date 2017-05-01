<?php

namespace Packages\Backup\App\Recurring;

use Illuminate\Routing\Router;
use App\Http\RouteServiceProvider;

/**
 * Routes regarding Servers.
 */
class RecurringRoutesProvider
    extends RouteServiceProvider
{
    /**
     * @var string
     */
    protected $package = 'backup';

    protected function api(Router $router)
    {
        $router->resource('recurring', RecurringController::class);
    }
}
