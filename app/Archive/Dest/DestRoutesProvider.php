<?php

namespace Packages\Backup\App\Archive\Dest;

use Illuminate\Routing\Router;
use App\Http\RouteServiceProvider;

/**
 * Routes regarding Servers.
 */
class DestRoutesProvider extends RouteServiceProvider
{
    /**
     * @var string
     */
    protected $package = 'backup';

    protected function api(Router $router)
    {
        $router->resource('destination', DestController::class);
    }
}
