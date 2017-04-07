<?php

namespace Packages\Backup\App\Backup;

use App\Http\RouteServiceProvider;
use Illuminate\Routing\Router;

/**
 * Routes regarding Servers.
 */
class ArchiveRoutesProvider
    extends RouteServiceProvider
{
    /**
     * @var string
     */
    protected $package = 'backup';

    /**
     * Setup Routes.
     */
//    public function bootRoutes()
//    {
//        $base = implode('.', ['pkg', $this->package, '']);
//        $this->sso->map(Archive::class, $base.'archive');
//    }

    protected function api(Router $router)
    {
        $router->resource('archive', ArchiveController::class);
    }
}