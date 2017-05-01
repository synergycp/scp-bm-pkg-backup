<?php

namespace Packages\Backup\App\Archive\Dest;

use Illuminate\Support\ServiceProvider;

class DestServiceProvider
extends ServiceProvider
{
    /**
     * @var array
     */
    protected $providers = [
        DestinationRoutesProvider::class
    ];

    /**
     * Register the feature's service providers.
     */
    public function register()
    {
        $this->app->singleton(Handler\HandlerService::class);

        collect($this->providers)->each(_one([$this->app, 'register']));
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            Handler\HandlerService::class,
        ];
    }
}
