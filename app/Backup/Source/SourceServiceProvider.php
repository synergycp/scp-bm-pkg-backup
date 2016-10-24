<?php

namespace Packages\Backup\App\Backup\Source;

use Illuminate\Support\ServiceProvider;

class SourceServiceProvider
extends ServiceProvider
{
    /**
     * @var array
     */
    protected $providers = [
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
