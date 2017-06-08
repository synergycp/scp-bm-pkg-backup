<?php

namespace Packages\Backup\App\Archive\Handler;

use Illuminate\Routing\Router;
use App\Http\RouteServiceProvider;

/**
 * Routes regarding Servers.
 */
class HandlerRoutesProvider
    extends RouteServiceProvider
{
    /**
     * @var string
     */
    protected $package = 'backup';

    protected function api(Router $router)
    {
        $router->resource('handler', HandlerController::class);
    }
}
