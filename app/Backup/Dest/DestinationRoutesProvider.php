<?php

namespace Packages\Backup\App\Backup\Dest;

use Illuminate\Routing\Router;
use App\Http\RouteServiceProvider;

/**
 * Routes regarding Servers.
 */
class DestinationRoutesProvider
    extends RouteServiceProvider
{
    /**
     * @var string
     */
    protected $package = 'backup';

    protected function api(Router $router)
    {
        $router->resource('destination', DestinationController::class);
    }
}